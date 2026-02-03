init:
	docker compose -f docker/docker-compose.yml up -d --build
	docker compose -f docker/docker-compose.yml run --rm app composer install

restart:
	docker compose -f docker/docker-compose.yml up -d

stop:
	docker compose -f docker/docker-compose.yml down

composer-install:
	docker compose -f docker/docker-compose.yml run --rm app composer install

composer-update:
	docker compose -f docker/docker-compose.yml run --rm app composer update

phpstan:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/phpstan analyse --memory-limit=256M

psalm:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/psalm
cs-check:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes

cs-fix:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/php-cs-fixer fix --allow-risky=yes

peck:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/peck

security-check:
	docker compose -f docker/docker-compose.yml run --rm app composer audit

lint: cs-fix phpstan psalm peck security-check

test:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/phpunit --colors=always

test-coverage:
	docker compose -f docker/docker-compose.yml run --rm app vendor/bin/phpunit --colors=always --coverage-text

bash:
	docker compose -f docker/docker-compose.yml run --rm app bash
