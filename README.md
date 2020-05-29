# Oracle NetSuite extractor - Proof of concept

[![Build Status](https://travis-ci.com/keboola/db-extractor-netsuite.svg?branch=master)](https://travis-ci.com/keboola/db-extractor-netsuite)

[KBC](https://www.keboola.com/product/) Docker app for extracting data from [Oracle NetSuite](https://www.netsuite.com/) tool.

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
