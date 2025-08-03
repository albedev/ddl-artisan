# DDL Artisan

ddl-artisan is a PHP tool designed to parse raw MySQL DDL files and automatically generate fully compatible Laravel migration files. It supports a wide variety of column types, indexes, foreign keys (including composite and cross-database), and modern Laravel migration syntax. Built for developers who want to speed up the migration process in existing or legacy databases.

---

## Installation
You can install the package via Composer:

```bash
composer require albedev/ddl-artisan
```

And... that's it! The package will automatically register itself in Laravel. To start the migration generation process, you can use the provided Artisan command.
```bash
php artisan ddl:generate path/to/your-ddl.sql
```

**Attention**: path/to/your-ddl.sql should be a valid MySQL DDL file and the path should be accessible by your Laravel application.

## Features

- Parses MySQL DDL statements into structured Laravel migrations
- Supports all major column types (INT, VARCHAR, TEXT, JSON, ENUM, etc.)
- Handles indexes: INDEX, UNIQUE, FULLTEXT, SPATIAL
- Handles foreign keys, including composite and cross-database
- CLI interface for instant usage (ddl-artisan generate)
- Easily installable via Composer

## Known Issues
- The package is still in development, so some features may not be fully implemented or tested.
- The parsing of complex DDL statements may not be perfect, especially for edge cases.
- Foreign keys migration has some issues with indentation and formatting, which will be fixed in future releases. (Doesn't affect functionality, just aesthetics)

## License

This project is licensed under the MIT-NR License - see the [LICENSE](LICENSE) file for details.