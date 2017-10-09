# eWay CRM writer

## What It Does

App helps you to write data objects to eWay CRM using the API. Use following configuration:

<pre>
{
  "webServiceAddress": "web-service-endpoint",
  "username": "your-account",
  "#password": "your-secret-password",
  "dieOnItemConflict": [true/false],
  "apiFunction": [saveCompany, saveProject]
}
</pre>

- `webServiceAddress` url endpoint   
- `username` is name of your account  
- `#password` is your secret password  
- `dieOnItemConflict` is flag to stop process when data conflicts
- `apiFunction` is one of provided fuctions for getting a data from CRM

## Required data fields:

The component has a limitation of data fields which are expected!

### Company
ItemGUID,ItemVersion,MRPID,CompanyName,IdentificationNumber,CompanyName2,Street,City,Country,OtherContact,PostalCode,VATNumer,Phone,Mobile,Mobile2,Fax,Email,Note,Department

### Project
ItemGUID,ItemVersion,CompanyGUID,MRPID,HID,ProjectName,ProjectStart,ProjectEnd,EstimatedPrice,Note,Note2
  

If you have any question contact support@bizztreat.com !

Cheers!