var pocketServer = {
    domain: document.domain,
    port: 7998,
    page: 'results.php'
};
$(document).ready(function () {
    // set DOM events
    var send = function () {
        // AJAX request for setting keys in database
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                key: document.getElementById('testKey').value,
                value: document.getElementById('testVal').value,
                password: document.getElementById('testPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#testData').html(data);
            }
        });
    };
    // send button or field entered
    $('#testSub').click(send);
    $('#testKey, #testVal, #testPwd').on('keyup', function (e) {
        if (e.which == 13 || e.keyCode == 13)
            send();
    });
    $('#testClr').click(function () {
        // AJAX request for clearing database
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                clear: true,
                password: document.getElementById('testPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#testData').html(data);
            }
        });
    });
    // set PocketJS events
    var snapshot = '';
    var pocket = Pocket();
    pocket.bind('update', function (db) {
        if (db != snapshot) {
            snapshot = db;
            db = JSON.parse(snapshot);
            console.log(db);
            var html = '';
            var bg = true;
            for (key in db) {
                bg = !bg;
                if (db.hasOwnProperty(key)) {
                    html += '<tr' + (bg ? " class = 'dark'" : '') + '><td>' + key + '</td><td>' + db[key] + '</td></tr>';
                }
            }
            $('#table table').html("<tr class = 'darker'><th>Name</th><th>Answer</th></tr>" + html);
        }
        if ((typeof db === 'string' && typeof snapshot === 'string') &&
            (db.trim() == '' || db.trim() == '{}' || db.trim() == '{ }') ||
            (snapshot.trim() == '' || snapshot.trim() == '{}' || snapshot.trim() == '{ }')
        ) $('#table table').html("<tr class = 'darker'><th>No Data</th></tr>");
    });
    pocket.onOpen(function () {
        $('#table table').html("<tr class = 'darker'><th>Connected</th></tr>");
    });
    pocket.onClose(function () {
        $('#table table').html("<tr class = 'darker'><th>Disconnected</th></tr>");
    });
    pocket.connect(pocketServer.domain, pocketServer.port, pocketServer.page);
    // keep server alive
    setInterval(function () {
        if (pocket.online())
            pocket.send('update');
    }, 500);
});
