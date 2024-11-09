<div>
    <style>
        body {
            margin: 0;
        }

        .container {
            width: 100%;
            display: flex;
            justify-content: space-between;

            .text {
                width: 45%;
                min-width: 700px;
                padding: 20px;
            }

            .buttons {
                width: 10%;
                min-width: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
                max-height: 500px;

                .container_buttons {
                    display: flex;
                    flex-direction: column;

                    button {
                        font-size: 16px;
                        margin: 10px;
                        border: none;
                        padding: 10px;
                        cursor: pointer;

                        transition: 500ms;
                    }

                    button:hover {
                        background-color: #c3c3c3;
                    }
                }
            }

            .answer {
                width: 45%;
                min-width: 700px;
                padding: 20px;
            }

            textarea {
                width: 100%;
                height: 100%;
                font-size: 16px;
            }
        }
    </style>

    <div class="container">
        <div class="text">
            <h2>Исходный код</h2>
            <textarea wire:model="text" rows="100"></textarea>
        </div>
        <div class="buttons">
            <div class="container_buttons">
                <button wire:click="parseLexems">Лексер</button>
                <button wire:click="parseSyntaxis">Синтаксер</button>
                <button wire:click="compile">Компилятор</button>
            </div>
        </div>
        <div class="answer">
            <h2>Ответ</h2>
            <textarea wire:model="ans" rows="100" readonly></textarea>
        </div>
    </div>
</div>
