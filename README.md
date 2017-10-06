# Logger module for Zend Framework

## Installation
If the `zendframework/zend-component-installer` package is installed it will ask you if you want to inject the module into your configs.
If not, the `Trinet\LoggerModule` has to be added manually to your `config/modules.config.php` file.

The default path for logged errors is `./data/log/`. The path must exist for the `Stream` lgo writer to be able to write files.

## Configuration
The module has two configuration options which can be overridden in a local config:
* `['trinet']['logger']['path']`: The path where log files should be written. Defaults to `./data/log/`.
*  `['trinet']['logger']['date-format']`: The date format of log entries. Defaults to `Y-m-d`.