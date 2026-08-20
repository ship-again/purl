SHELL := /bin/sh

QA_BIN ?= /opt/qa/vendor/bin
PHPUNIT ?= vendor/bin/phpunit

.PHONY: install test coverage cs-check cs-fix psalm qa ci-quality

install:
	composer install --no-interaction --prefer-dist

test:
	$(PHPUNIT) --configuration phpunit.xml.dist

coverage:
	mkdir -p build/coverage
	XDEBUG_MODE=coverage $(PHPUNIT) --configuration phpunit.coverage.xml.dist --coverage-clover build/coverage/clover.xml

cs-check:
	$(QA_BIN)/php-cs-fixer fix --dry-run --diff --using-cache=yes

cs-fix:
	$(QA_BIN)/php-cs-fixer fix --using-cache=yes

psalm:
	mkdir -p build/reports
	$(QA_BIN)/psalm --report=build/reports/psalm.sonarqube.json --report-show-info=false

qa: cs-check coverage psalm

ci-quality: install cs-check coverage
	mkdir -p build/reports
	@set +e; \
	$(QA_BIN)/psalm --report=build/reports/psalm.sonarqube.json --report-show-info=false; \
	status=$$?; \
	printf '%s\n' "$$status" > build/reports/psalm.exit; \
	exit 0
