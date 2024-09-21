<?php

namespace App\Modules\Absolute\Job;

use App\Modules\Absolute\Model\AbsoluteProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AbsoluteParserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $baseUrl = 'https://www.tgabsolut-shop.ru';

    private string $body;

    public function __construct(
        private int $category,
        private int $index,
        private int $page
    )
    {
        $this->onQueue('main');
    }

    public function handle()
    {
        $this->getBody();

        $this->getItems();

        if ($this->page < 27)
        {
            $this->dispatch($this->category, $this->index, $this->page + 1)->delay(Carbon::now()->addMinutes(rand(10, 20)));
        }
    }

    private function getBody()
    {
        $request = Http::withHeaders([
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language: ru,en;q=0.9,la;q=0.8,eu;q=0.7,es;q=0.6',
            'Cache-Control: max-age=0',
            'Sec-Ch-Ua: "Not_A Brand";v="8", "Chromium";v="120", "YaBrowser";v="24.1", "Yowser";v="2.5"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1',
        ])->withUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 YaBrowser/24.1.0.0 Safari/537.36'
        )->maxRedirects(20);

        $request->withCookies([
            'BX_USER_ID' => '8aa46bd57973c2a347af12a4be1b530b',
            'BITRIX_SM_AG_SMSE_H' => 'Гренки',
            'thKostyl7' => 'lobotomirovan',
            'thSklad5' => 'Ц0003',
            'thSimpleAddress2' => 'г Иркутск, ул Гоголя, д 81',
            'thMinSum1' => '800',
            'thMinSum2' => '1000',
            'thDeliveryPrice1' => '100',
            'thDeliveryPrice4' => '100',
            'PHPSESSID' => 'dGI3Ljn7qCe4UoPZzpNu6YIeAJY4abya',
            'BITRIX_SM_BXMAKER_AUP_GID2' => '55148349',
            'BITRIX_CONVERSION_CONTEXT_s1' => '{"ID":1,"EXPIRE":1724601540,"UNIQUE":["conversion_visit_day"]}',
        ], 'www.tgabsolut-shop.ru');

        $response = $request->get($this->baseUrl . '/catalog/' . $this->category . '/?sort=catalog_PRICE_11&method=asc&PAGEN_' . $this->index . '=' . $this->page);

        Log::debug('http response', [$response->status(), $response->headers(), $response->body()]);

        $this->body = $response->body();
    }

    private function getItems()
    {
        $str = $this->body;

        $items = [];

        do
        {
            $item = Str::betweenFirst($str, '<div class="product-item">', '<div class="labelBlock">');

            if ($item !== '')
            {
                $product = $this->parseItem($item);

                if ($product)
                {
                    $items[] = $product;
                    AbsoluteProduct::saveProduct($product);
                }

                $ini = strpos($str, '<div class="product-item">');
                $str = substr($str, $ini + Str::length($item), Str::length($str) - Str::length($item) - $ini);
            }
        }
        while ($item !== '');
    }

    private function parseItem(string $item)
    {
        $title = Str::betweenFirst($item, 'title="', '"');

        if ($title === '=')
        {
            return null;
        }

        $price = 0;
        $priceStr = Str::betweenFirst($item, 'product-item-price-current', '</div>');

        try
        {
            $price = (float)Str::betweenFirst($priceStr, '">', '&');
        }
        catch (\Exception $e)
        {
            dump($e);
            dump($priceStr);
        }

        $category = $this->category;

        $imageStr = Str::between($item, ' url(', ');"');

        $image = $this->baseUrl . '/' . substr($imageStr, 1, Str::length($imageStr) - 2);

        return [
            'item' => $item,
            'name' => $title,
            'price' => $price,
            'category' => $category,
            'image' => $image,
        ];
    }
}
