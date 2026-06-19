.PHONY: dev

PORT ?= 8082

dev:
	pnpm exec concurrently \
		-c "blue,magenta" \
		-n "laravel,vite" \
		"php artisan serve --port=$(PORT)" \
		"pnpm dev"
