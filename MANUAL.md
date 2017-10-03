# eWay CRM extractor

## What It Does

This helps you to extract any data objects from eWay CRM using the API. Use following configuration:

<pre>
{
  "webServiceAddress": "web-service-endpoint",
  "username": "your-account",
  "#password": "your-secret-password",
  "passwordAlreadyEncrypted": [true/false],
  "dieOnItemConflict": [true/false],
  "apiFunction": [getCompanies, getProjects]
}
</pre>


- `webServiceAddress` url endpoint   
- `username` is name of your account  
- `#password` is your secret password
- `passwordAlreadyEncrypted` is flag if you provide already encrypted password (use always FALSE) pasword is encrypted inside KBC  
- `dieOnItemConflict` is flag to stop process when data conflicts
- `apiFunction` is one of provided fuctions for getting a data from CRM  

If you have any question contact support@bizztreat.com !

Cheers!