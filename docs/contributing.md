# Contributing

We welcome contributions to the project! Whether you're fixing a bug, adding a feature, or improving documentation, your help is appreciated.

## How to Contribute
1. Fork the repository on GitHub
2. Clone your fork
   ```bash
   git clone https://github.com/your-username/laravel-auth-logs.git
   ```
3. Create a feature branch
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. Make your changes and add tests when applicable
5. Run the test suite
   ```bash
   composer test
   ```
6. Commit and push
   ```bash
   git add .
   git commit -m "feat: add your commit message here"
   git push origin feature/your-feature-name
   ```
7. Open a pull request with a clear description of your changes

## Local Testing (Package Development)
1. Clone the repository (if you haven’t already)
   ```bash
   git clone https://github.com/akira-io/laravel-auth-logs.git
   cd laravel-auth-logs
   composer install
   ```
2. Autoload refresh during development
   ```bash
   composer dump-autoload
   ```
3. Alternatively, link via path repository in your app’s `composer.json`
   ```json
   "repositories": [
     { "type": "path", "url": "/path/to/laravel-auth-logs" }
   ]
   ```
   Then require it:
   ```bash
   composer require akira/laravel-auth-logs
   ```

See the installation process in ./installation.md

## Helpful Resources
- Laravel: https://laravel.com/docs
- Composer: https://getcomposer.org/doc/
- GitHub PR guide: https://docs.github.com/en/github/collaborating-with-issues-and-pull-requests
