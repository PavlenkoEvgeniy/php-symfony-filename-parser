init: composer-install

composer-install:
	composer install

composer-update:
	composer update

phpstan:
	vendor/bin/phpstan analyse --memory-limit=256M

psalm:
	vendor/bin/psalm

cs-check:
	vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

peck:
	vendor/bin/peck

security-check:
	composer audit

lint: cs-check phpstan psalm peck security-check

test:
	vendor/bin/phpunit --colors=always

test-coverage:
	vendor/bin/phpunit --colors=always --coverage-text
