var pocketServer = {
    domain: document.domain,
    port: 7998,
    page: 'results.php'
};
$(document).ready(function () {
    // set data DOM events
    $('#testSub').click(function () {
        // AJAX request for setting keys in database
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                name: document.getElementById('testKey').value,
                answer: document.getElementById('testVal').value,
                password: document.getElementById('testPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#testData').html(data);
            }
        });
    });
    $('#testKey, #testVal, #testPwd').on('keyup', function (e) {
        if (e.which == 13 || e.keyCode == 13)
            $('#testSub').click();
    });
    // set admin DOM events
    $('#adminClr').click(function () {
        // AJAX request for clearing database
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                clear: true,
                password: document.getElementById('adminPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#adminData').html(data);
            }
        });
    });
    $('#adminDel').click(function () {
        // AJAX request for deleting keys in database
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                number: document.getElementById('adminNum').value,
                password: document.getElementById('adminPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#adminData').html(data);
            }
        });
    });
    $('#adminNum').on('keyup', function (e) {
        if (e.which == 13 || e.keyCode == 13)
            $('#adminDel').click();
    });
    $('#adminAns').click(function () {
        // AJAX request for setting correct answer
        $.ajax({
            url: window.phpServer,
            method: 'POST',
            async: true,
            data: {
                correct: document.getElementById('adminCor').value,
                password: document.getElementById('adminPwd').value
            },
            dataType: 'text',
            success: function (data) {
                $('#adminData').html(data);
            }
        });
    });
    $('#adminCor').on('keyup', function (e) {
        if (e.which == 13 || e.keyCode == 13)
            $('#adminAns').click();
    });

    // set pocketjs events
    var snapshot = '';
    var pocket = Pocket();
    pocket.bind('update', function (db) {
        if (db != snapshot) {
            snapshot = db;
            db = JSON.parse(snapshot);
            console.log(db);
            $('#table table').html("<tr class = 'darker'><th>ID</th><th>Name</th><th>Answer</th><th>Correct</th></tr>");
            var bg = true;
            for (i in db) {
                bg = !bg;
                var item = db[i];
                var row = document.createElement('tr');
                if (bg) row.className = 'dark';
                var num = document.createElement('td');
                num.className = 'num';
                num.appendChild(document.createTextNode(String(parseInt(i) + 1)));
                row.appendChild(num);
                var name = document.createElement('td');
                name.appendChild(document.createTextNode(item.name));
                row.appendChild(name);
                var answer = document.createElement('td');
                answer.appendChild(document.createTextNode(item.answer));
                row.appendChild(answer);
                var correct = document.createElement('td');
                correct.appendChild(document.createTextNode((item.correct == 'true') ? 'YES' : 'NO'));
                row.appendChild(correct);
                $('#table table')[0].appendChild(row);
            }
        }
        if ((typeof db === 'string' && typeof snapshot === 'string') &&
            (db.trim() == '' || db.trim() == '[]' || db.trim() == '[ ]') ||
            (snapshot.trim() == '' || snapshot.trim() == '[]' || snapshot.trim() == '[ ]')
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
    }, 450);
});
