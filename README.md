# Introduction

[![Version](http://img.shields.io/packagist/v/govtnz/silverstripe-sqlexplorer.svg?style=flat)](https://packagist.org/packages/govtnz/silverstripe-sqlexplorer)
[![License](http://img.shields.io/packagist/l/govtnz/silverstripe-sqlexplorer.svg?style=flat)](LICENSE.md)

SQL Explorer is a simple module that provides read-only access to database tables, and read-only SQL statements. It is 
intended for deployment in production environments, with restricted user access.

The tools is designed to facilite adhoc queries on a production database, where access is otherwise limited.

# WARNING

Before installing this module, it is recommended that you consult with technical governance within your organisation 
as applicable.

# Restrictions

The tool imposes some restrictions from a security perspective:

 *  SQL statements must be SELECT statements.
 *  Certain table columns are excluded automatically from results, such as
    password hashes, salt, etc. In a future release this may be configurable.


# Configuration

No configuration is required. However, you can set the following in config.yml:

 *  require_explicit_permission     if truthy, the SQL Explorer admin interface
                requires users explicitly have SQL_EXPLORER persmission. Without this extra control, any administrator can access it
                directly, which in some environment is undesirable.
                Default is false.

Future state:

 *  Whitelist/blacklist specific tables or columns on tables.
