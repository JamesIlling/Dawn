<?php
$databaseConnection = mysqli_connect("localhost", "root", "", "dawn", 7188);
if (mysqli_connect_errno()) {
    echo "failed to connect to MySql";
}
$sql = "INSERT INTO site (name, description) VALUES('Ankara Citadel','The citadel at Ankara')";
if (mysqli_query($databaseConnection, $sql)) {
    echo "site: 'Ankara Citadel' Added <br/>";
} else {
    echo mysqli_error($databaseConnection);
}

$sql = "INSERT INTO site (name, description) VALUES('Cadir Hüyük','artificial mound in central Turkey that contains the remains of some 6,000 years of human settlement')";
if (mysqli_query($databaseConnection, $sql)) {
    echo "site: 'Cadir Hüyük' Added <br/>";
} else {
    echo mysqli_error($databaseConnection);
}

$sql = "INSERT INTO site (name, description) VALUES('Bogazköy','The village in north central Turkey (köy is Turkish for village), situated on the River Halys, which turned out to be the site of Hattusas, capital of the ancient Hittite Empire.')";
if (mysqli_query($databaseConnection, $sql)) {
    echo "site: 'Bogazköy' Added <br/>";
} else {
    echo mysqli_error($databaseConnection);
}

$sql = "INSERT INTO site (name, description) VALUES('Perge','Ancient Greek city in Anatolia and the capital of Pamphylia')";
if (mysqli_query($databaseConnection, $sql)) {
    echo "site: 'Perge' Added <br/>";
} else {
    echo mysqli_error($databaseConnection);
}

$sql = "INSERT INTO trench (name, description,siteId) VALUES('Trench1','description',1)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('Trench2','description',1)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('Trench3','description',1)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchA','description',2)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchB','description',2)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchC','description',2)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchX','description',3)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchY','description',3)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('TrenchZ','description',3)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('Apostles','description',4)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('St Barnabus','description',4)";
mysqli_query($databaseConnection, $sql);
$sql = "INSERT INTO trench (name, description,siteId) VALUES('St Paul','description',4)";
mysqli_query($databaseConnection, $sql);

?>