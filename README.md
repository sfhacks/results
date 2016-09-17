# sfhacks results
Web interface for collecting results and answers from students
&nbsp;  
## How to submit answers
Send an HTTP POST request to [results.sfhacks.club](http://results.sfhacks.club) with the following data:
 * key=*YourName*
 * value=*YourAnswer*

## Requests (examples)
Bash cURL:
```bash
curl -X 'POST' -d "key=Joe&value=a45d&password=sfhacks" http://results.sfhacks.club
```  
&nbsp;  
jQuery AJAX:
```javascript
$.ajax({
    url: 'http://results.sfhacks.club',
    method: 'POST',
    data: {
        key: 'Joe',
        value: 'a45d',
        password: 'sfhacks'
    },
    dataType: 'text',
    success: function (data) {
        console.log(data);
    }
});
```
&nbsp;  
Pure JS AJAX:
```javascript
var xhr = new XMLHttpRequest();
xhr.onload = function () {
    console.log(xhr.responseText);
}
xhr.open('POST', 'http://results.sfhacks.club', true);
request.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
request.send({
    key: 'Joe',
    value: 'a45d',
    password: 'sfhacks'
});
```
&nbsp;  
