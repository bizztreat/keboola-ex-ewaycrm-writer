# keboola-ex-ewaycrm-reader
This app serves as an writer. Calls an API endpoint of the eWay CRM systems to write data into the CRM system.

# Checklist

0.   bizztreat
1.   eWay CRM reader
2.   extractor
3.   App provide features to read data from eWay API
4.   
5.   ./eway-logo.png
6.   Bizztreat s.r.o.
7.   Bubenská 1477/1, Praha, CZ
8.   david.chobotsky@bizztreat.com
9.   https://github.com/bizztreat/keboola-ex-ewaycrm-writer/blob/master/LICENSE.md
10.  https://hub.docker.com/r/bizztreat/keboola-ex-ewaycrm-writer/
11.  latest
12.  (default)
13.  (default)
16.  true
17.  false
18.  in
19.  false
20.  https://github.com/bizztreat/keboola-ex-ewaycrm-writer/blob/master/MANUAL.md
21.  tableOutput
22.  see below ...
23.  see below ...
24.  see below ...
25.  dataIn
26.  run
27.  false
28.  none
29.  standard
30.  local

###Test configuration
```
{
    "storage": {
        "output": {
            "tables": [
                {
                    "source": "destination.csv",
                    "destination": "out.c-eway.test",
                    "incremental": false,
                    "primary_key": [],
                    "columns": [],
                    "delete_where_values": [],
                    "delete_where_operator": "eq",
                    "delimiter": ",",
                    "enclosure": "\"",
                    "metadata": [],
                    "column_metadata": []
                }
            ],
            "files": []
        }
    },
    "parameters": {
        "webServiceAddress": "https:\/\/trial.eway-crm.com\/<id>\/WcfService\/Service.svc\/",
        "username": "<user>",
        "#password": "<pwd>",
        "passwordAlreadyEncrypted": false,
        "dieOnItemConflict": false,
        "apiFunction": "getCompanies"
    },
    "processors": {
        "before": [],
        "after": []
    },
    "image_parameters": [],
    "action": "run"
}
```

### Configuration schema
```
{
  "configurationSchema": {
    "type": "object",
    "title": "Parameters",
    "required": [
      "webServiceAddress",
      "username",
      "#password",
      "apiFunction"
    ],
    "properties": {
      "webServiceAddress": {
        "type": "string",
        "title": "API endpoint",
        "default": "",
        "minLength": 1
      },
      "username": {
        "type": "string",
        "title": "Username",
        "default": "",
        "minLength": 1
      },
      "#password": {
        "type": "string",
        "title": "Password",
        "format": "password",
        "default": "",
        "minLength": 4
      },
      "apiFunction": {
        "type": "string",
        "title": "API Function",
        "default": "",
        "minLength": 1
      },
      "passwordAlreadyEncrypted": {
        "type": "boolean",
        "title": "Password Already Encrypted",
        "format": "checkbox",
        "default": "false"
      },
      "dieOnItemConflict": {
        "type": "boolean",
        "title": "Die On Item Conflict",
        "format": "checkbox",
        "default": "false"
       }
    }
  }
}
```
[Details here](https://github.com/bizztreat/keboola-ex-ewaycrm-writer/blob/master/MANUAL.md)