.PHONY: help add-user up down build migrate

# Default target
help:
	@echo "Available commands:"
	@echo "  make add-user NAME=name EMAIL=email    Add a new user to the database"
# Add a user to the database
add-user:
	@if [ -z "$(NAME)" ] || [ -z "$(EMAIL)" ]; then \
		echo "Usage: make add-user NAME='John Doe' EMAIL='john@example.com'"; \
		exit 1; \
	fi
	@echo "Adding user: $(NAME) ($(EMAIL))"
	docker compose exec app php artisan tinker --execute="App\Models\User::create(['name' => '$(NAME)', 'email' => '$(EMAIL)', 'password' => bcrypt('password')]); echo 'User created successfully';"
