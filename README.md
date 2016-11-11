# sfhacks results
Web interface for collecting results and answers from students  
&nbsp;  
Libraries Used:  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[jQuery](https://jquery.com/) - v3.1.0  
&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;[pocketjs](http://anuv.me/pocketjs) - v1.0 (by [@anuvgupta](https://github.com/anuvgupta))
&nbsp;  
## Submitting Answers
Send an HTTP POST request to [results.sfhacks.club](http://results.sfhacks.club) with the following data:
 * name=*YourName*
 * answer=*YourAnswer*
 * password=sfhacks

### Request Examples
Bash cURL:
```bash
curl -X 'POST' -d "name=Joe&answer=a45d&password=sfhacks" http://results.sfhacks.club
```  
&nbsp;  
jQuery AJAX:
```javascript
$.ajax({
    url: 'http://results.sfhacks.club',
    method: 'POST',
    data: {
        name: 'Joe',
        answer: 'a45d',
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
xhr.send('name=Joe&answer=a45d&password=sfhacks');
```
&nbsp;  

## Admin Actions
The admin password is an environment variable stored on [results.sfhacks.club](http://results.sfhacks.club).  
&nbsp;&nbsp; (Email [me@anuv.me](mailto:me@anuv.me) for access to the password)
 * Deleting Database Entries
    * A parameter `number` (which specifies the number ID of the entry) must be provided
    * A parameter `password` must be the admin password
    * Query Format
       * number=*entry_ID*
       * password=*admin_password*
    * Example: `number=2&password=secret`
 * Setting the Correct Answer
    * A parameter `correct` (which specifies the new correct answer) must be provided
        * If `correct` is set to `__N/A`, then the **Correct** column on [results.sfhacks.club](http://results.sfhacks.club) is set to N/A
            * This feature can be used to "hide" the correct answer if there is no particular correct answer, or if it should not be shown yet
    * A parameter `password` must be the admin password
    * Query Format
       * correct=*new_answer*
       * password=*admin_password*
    * Example: `correct=a45d&password=secret`
 * Clearing the Database
    * A parameter `clear` must be provided and set to `true`
    * A parameter `password` must be the admin password
    * Query Format
      * clear=true
      * password=*admin_password*
    * Example: `clear=true&password=secret`

&nbsp;  

## Structure
Four main components of the results application:
 1. `db.json` database of names/answers
 2. `results.php` pocketjs WebSocket server
    * uses `pocket.php` to create WebSocket server
    * runs indefinitely on [ws://results.sfhacks.club:7998](http://results.sfhacks.club)
    * checks `db.json` repeatedly (every second) for changes
    * when changes found, pushes updated database to all open clients
 3. `index.php` HTTP server/web client
    * server: accepts HTTP POST requests as defined [here](#submitting-answers)
        * updates keys in database based on posted data
        * logic is defined in `api.php`
    * client: page seen on [http://results.sfhacks.club](http://results.sfhacks.club)
        * displays table based on database (styled by `style.css`)
        * uses `app.js` to send and receive updates
 4. `app.js` powers web client for the HTTP and pocketjs servers
    * uses jQuery AJAX to send data to `index.php`
        * password for sending answers is `sfhacks`
        * password for admin actions is a secret!
    * uses pocketjs WebSocket client
        * receives changes in `db.json` from `results.php`

&nbsp;  
