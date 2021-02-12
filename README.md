# StudentApi

StudentApi is a PHP/Symfony Api to handle student grades using Symdony v5 and FOSRestBundle.


## Prerequisites

Create a `.env` file in the root of your project :
```
# .env
APP_ENV=dev
APP_SECRET=A_SECRET_PHRASE
# the user:password will change in production
DATABASE_URL="postgresql://user:password@db:5432/testdb"
```

## Installation

You have two methods to run the project : 
### Using Docker
You can simply use docker : 
```
cd infra/
docker-compose up --build
```
Then the server is accessible at `http://localhost:8000/`

### Using symfony server
With this method, you have to run a postgresql server, and change the `DATABASE_URL` parameter in the .env file.  
Use the package manager [composer](https://getcomposer.org/) to install the dependencies.

```
composer install
symfony serve
```
Then the server is accessible at `http://localhost:8000`

## Usage

The api doc is accessible at the address `http://localhost:8000/api/doc` (with docker)

You can use [postman](https://www.postman.com/) to send your request, or a simple curl, example :
```
curl --location --request POST 'localhost:8000/api/student' --form 'firstname="Meriadoc"' --form 'lastname="Brandibouc"' --form 'birthdate="1970-01-01"'
```

## Fake Datas

There is a command to create fake datas.  

Docker usage :
```
cd infra/
docker-compose exec php php bin/console app:create-user-grades
```
Symfony serve usage : `php bin/console app:create-user-grades`

## Tests

You can run tests with this command :
`php bin/phpunit`  
To clean the data, a command has been created : `php bin/console app:clean-tests-datas`  
If you use Docker, you can launch it with : 
```
docker-compose exec php php bin/console app:clean-tests-datas
```