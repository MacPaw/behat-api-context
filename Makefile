.PHONY: cs-fix rector phpunit phpstan phpcs dev-checks

cs-fix:
	composer run code-style-fix

rector:
	@if [ -x vendor/bin/rector ]; then vendor/bin/rector --dry-run; else echo "rector not installed; skipping"; fi

phpunit:
	composer run phpunit

phpstan:
	composer run phpstan

phpcs:
	composer run code-style

dev-checks:
	composer run dev-checks
