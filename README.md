# Exercise

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `make build` to build fresh images
3. Run `make up` to start the container
4. Run `make hooks-install` to install git hooks
4. Run `make bash` to enter into the container
5. Run `make down` to stop the Docker containers.

## Tests
Run `make test` to run the tests.

## coding style
Run `make cs` to run all the coding style tools<br>
or <br>
Run `make cs-phpstan` to run phpstan (php)<br>
Run `make cs-deptrac` to run deptrac (dependencies check)<br>
Run `make ci-cs-fixer` to run php-cs-fixer (coding standards)

## commands
1. generate php file from xml file with classes<br>
Run `make bash` <br>
Then run `bin/console app:php-class-generator:from-xml sample1.xml` <br>


