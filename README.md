# sfhacks results
Web interface for collecting results and answers from students  
&nbsp;  
Libraries Used:  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[jQuery](https://jquery.com/) - v3.1.0  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[PocketJS](http://anuv.me/pocketjs) - v1.0 (by [@anuvgupta](https://github.com/anuvgupta))
&nbsp;  
## How to submit answers
Send an HTTP POST request to [results.sfhacks.club](http://results.sfhacks.club) with the following data:
 * key=*YourName*
 * value=*YourAnswer*
 * password=sfhacks

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
    success: function (data) {
        console.log(data);
    }
});
```
&nbsp;  
Pure JS AJAX:
```javascript
var xhr = new XMLHttpRequest();
xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200)
        console.log(xhr.responseText);
};
xhr.open('POST', 'http://results.sfhacks.club', true);
xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
xhr.send('key=Joe&value=a45d&password=sfhacks');
```
&nbsp;  
