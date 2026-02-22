.PHONY: help install test phpstan cs-fix cs-check infection deptrac quality security-check lint ci

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
	composer install

test: ## Run PHPUnit tests
	vendor/bin/phpunit

phpstan: ## Static analysis (level 9)
	vendor/bin/phpstan analyse --memory-limit=512M

cs-fix: ## Fix code style
	vendor/bin/php-cs-fixer fix

cs-check: ## Check code style (no fix)
	vendor/bin/php-cs-fixer fix --dry-run --diff

infection: ## Mutation testing (run after adding tests that cover src/; needs coverage)
	vendor/bin/infection --only-covered --show-mutations --threads=max --min-msi=65 --ignore-msi-with-no-mutations

deptrac: ## Architecture enforcement
	vendor/bin/deptrac analyse

security-check: ## Dependency security audit
	composer audit --abandoned=report

lint: ## Lint config files and composer
	composer validate --strict
	@test -d config && find config -name '*.xml' -exec xmllint --noout {} + 2>/dev/null || true
	@test -d Resources && find Resources -name '*.xml' -exec xmllint --noout {} + 2>/dev/null || true

quality: cs-check phpstan deptrac lint test infection ## Full quality pipeline

ci: security-check quality ## Full CI pipeline (security + quality)
