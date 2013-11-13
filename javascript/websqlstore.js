function opendb() {
    try {
        if (!window.openDatabase) {
            alert('not supported');
        } else {
            var shortName = 'mydatabase';
            var version = '1.0';
            var displayName = 'My Important Database';
            var maxSize = 65536; // in bytes
            var db = openDatabase(shortName, version, displayName, maxSize);

            // You should have a database in
            return db;
        }
    } catch (e) {
        // Error handling code goes here.
        if (e == 2) {
            // Version number mismatch.
            alert("Invalid database version.");
        } else {
            alert("Unknown error " + e + ".");
        }
        return;
    }

}

function createTables(db) {
    db.transaction(
        function (transaction) {

            /* The first query causes the transaction to (intentionally) fail if the table exists. */
            transaction.executeSql('CREATE TABLE site(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL DEFAULT "");', [], nullDataHandler, errorHandler);


            transaction.executeSql('CREATE TABLE trencg(id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, siteid INTEGER NOT NULL);', [], nullDataHandler, errorHandler);
        }

    db.transaction(
        function (transaction) {
            foreach(
            var site
            in
            sites
            )
            transaction.executeSql('INSERT into sites (id,name) VALUES ( ?,? );',
                [ site.value, site.text ],
                function (transaction, resultSet) {
                    if (!resultSet.rowsAffected) {
                        // Previous insert failed. Bail.
                        alert('No rows affected!');
                        return false;
                    }
                    foreach(
                    var trench
                    in
                    site.trenches
                    )
                    {
                        transaction.executeSql('INSERT into trenches (name, id, siteId) VALUES (?, ?, ?);',
                            [ trench.text, trench.value, site.value ],
                            nullDataHandler, errorHandler);
                    }
                    ,
                    errorHandler
                    )
                    ;
                }
        }, transactionErrorCallback, proveIt);
}
}