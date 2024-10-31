# php-test 

## Getting Started

### Prerequisites
- [PHP](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/download/)

### Installation

1. Клонируйте репозиторий:
   ```bash
   git clone https://github.com/physostegia/php-test
2. Перейдите в директорию проекта:
   ```bash
   cd php-test
3. Установите зависимости с помощью Composer(их нет):
   ```bash
   composer install
4. Установите значение пути к исходному списку
   ```bash
   bracketInput = $csvHandler->ReadCsv("путь-к-сетке.csv");
5. Установите значение пути к выходному списку
   ```bash
   $csvHandler->WriteCsv("путь-к-выходной-сетке.csv", $outputBracket);
6. Запустите проект:
   ```bash
   composer run

