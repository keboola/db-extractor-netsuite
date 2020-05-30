# Oracle NetSuite extractor - Proof of concept

[![Build Status](https://travis-ci.com/keboola/db-extractor-netsuite.svg?branch=master)](https://travis-ci.com/keboola/db-extractor-netsuite)

[KBC](https://www.keboola.com/product/) Docker app for extracting data from [Oracle NetSuite](https://www.netsuite.com/) tool.

## Configuration

The configuration `config.json` contains following properties in `parameters` key: 

*Note:* `query` or `table` must be specified.

- `db` - object (required): Connection settings
    - `accountId` - string (required)
    - `roleId` - string (required)
    - `user` - string (required): User with correct access rights
    - `#password` - string (required): Password for given `user`
- `query` - string (optional): SQL query whose output will be extracted
- `table` - object (optional): Table whose will be extracted
    - `tableName` - string (required)
    - `schema` - string (required)
- `columns` - array (optional): List of columns to export (default all columns)
- `outputTable` - string (required): Name of the output table 
- `incremental` - bool (optional):  Enables [Incremental Fetching](https://help.keboola.com/components/extractors/database/#incremental-fetching)
- `incrementalFetchingColumn` - string (optional): Name of column for [Incremental Fetching](https://help.keboola.com/components/extractors/database/#incremental-fetching)
- `incrementalFetchingLimit` - integer (optional): Max number of rows fetched per one run
- `primaryKey` - string (optional): Sets primary key to specified column in output table
- `retries` - integer (optional): Number of retries if an error occurred

## Example configuration

### Get Tables
```json
{
  "action": "getTables",
  "parameters": {
    "db": {
      "accountId": "....",
      "roleId": "....",
      "user": "....",
      "#password": "...."
    },
    "tableListFilter": {
      "listColumns": true,
      "tablesToList": [
        {
          "tableName": "Account",
          "schema": "default"
        }  
      ]
    }
  }
}
```

### Run

```json
{
  "parameters": {
    "db": {
      "accountId": "....",
      "roleId": "....",
      "user": "....",
      "#password": "...."
    },
    "query": "SELECT InternalId,CurrencyPrecision,DisplaySymbol FROM Currency WHERE InternalId < 10",
    "outputTable": "in.c-main.currency",
    "primaryKey": []
  }
}
```

## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/db-extractor-netsuite
cd db-extractor-netsuite
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 
