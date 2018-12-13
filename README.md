# EmailValidator

The goal of this project is to extend symfony console by adding a new command that allows to validate input CSV file and produce two CSV files: one with valid and another with invalid email addresses.

## Installation


```
git clone https://github.com/yohaulticetll/validate-emails.git
cd validate-emails/
composer update
```

### Usage Example

```
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new \App\Command\ValidateEmailsCommand(new \App\Service\EmailValidator()));
$application->run();
```

### Calling from console

```
php console/validate app:validate-emails <filePath> <outputDirectory>
```
