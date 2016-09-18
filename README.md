# sfhacks results
Web interface for collecting results and answers from students  
 * `db.json` database of keys/values
 * `results.php` pocketjs socket server
   * uses `pocket.php` to create socket server
   * runs indefinitely on [ws://results.sfhacks.club:7998](http://results.sfhacks.club)
   * checks `db.json` for changes when asked for updates by clients
   * when changes found, pushes updated database to all open clients
 * `index.php` server for updating database/web client for server
   * server: accepts HTTP POST requests as defined [here](#submitting-answers)
   * client: page seen on [http://results.sfhacks.club](http://results.sfhacks.club)
     * displays table based on database (styled by `style.css`)
     * uses `app.js` to send and receive updates
  * `app.js` communicator for AJAX and pocketjs servers
    * uses jQuery AJAX to send data to `index.php`
      * password for setting keys is `sfhacks`
      * password for clearing database is a secret!
     * uses pocketjs WebSocket client
       * requests/receives changes in `db.json` from `results.php`

&nbsp;  
Libraries Used:  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[jQuery](https://jquery.com/) - v3.1.0  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[pocketjs](http://anuv.me/pocketjs) - v1.0 (by [@anuvgupta](https://github.com/anuvgupta))
&nbsp;  
## Submitting Answers
Send an HTTP POST request to [results.sfhacks.club](http://results.sfhacks.club) with the following data:
 * key=*YourName*
 * value=*YourAnswer*
 * password=sfhacks

### Request Examples
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
