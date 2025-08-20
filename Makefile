.PHONY: help
help: ## Show this help.
	@printf "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m\n"
	@grep -E '^[-a-zA-Z0-9_\.\/]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: coding-style/check
coding-style/check: ## Lints the whole project according to coding standards
	vendor/bin/php-cs-fixer fix --diff --dry-run

.PHONY: coding-style/fix
coding-style/fix: ## Fixes the whole project according to coding standards
	vendor/bin/php-cs-fixer fix --diff -vv

.PHONY: static-analysis
static-analysis: ## Run static analysis
	vendor/bin/phpstan analyze

.PHONY: check
check: coding-style/check