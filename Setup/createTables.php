<?php

// log in to server.
$databaseConnection = mysqli_connect("localhost", "root", "", "", 7188);
if (mysqli_connect_errno()) {
    echo "failed to connect to MySql";
}

// create the dawn database
$sql = "CREATE DATABASE dawn";
if (mysqli_query($databaseConnection, $sql)) {
    echo "Database created <br/>";
    mysqli_close($databaseConnection);
    $databaseConnection = mysqli_connect("localhost", "root", "", "dawn", 7188);
} else {
    echo "error creating database";
    return;
}

$sql = "CREATE TABLE site (id INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(id), name VARCHAR(50),description VARCHAR(200),coordinates VARCHAR(30))";
if (mysqli_query($databaseConnection, $sql)) {
    echo "site table created <br/>";
} else {
    printf("Error creating trenches table: %s\n", mysqli_error($databaseConnection));
}

$sql = "CREATE TABLE trench (id INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(id), name VARCHAR(50),description VARCHAR(200),coordinates VARCHAR(30),siteId INT NOT NULL,FOREIGN KEY (siteId) REFERENCES site(id))";
if (mysqli_query($databaseConnection, $sql)) {
    echo "trenches table created <br/>";
} else {
    printf("Error creating trenches table: %s\n", mysqli_error($databaseConnection));
}

$sql = "CREATE TABLE find (id INT NOT NULL AUTO_INCREMENT,PRIMARY KEY(id), trenchId INT NOT NULL,FOREIGN KEY (trenchId) REFERENCES trench(id), findNumber VARCHAR(200),context VARCHAR(200),numberOfSherds VARCHAR(200),coordinates VARCHAR(200),sherdType VARCHAR(200),fabricType VARCHAR(200),fabricTypeCode VARCHAR(200),wareType VARCHAR(200),baseType VARCHAR(200),rimType VARCHAR(200),fabricColour VARCHAR(200),construction VARCHAR(200),height VARCHAR(200),width VARCHAR(200),thickness VARCHAR(200),weight VARCHAR(200),rimDiameter VARCHAR(200),baseDiameter VARCHAR(200),surfaceTreatment VARCHAR(200),temperType VARCHAR(200),temperQuality VARCHAR(200),manufacture VARCHAR(200),sherdCondition VARCHAR(200),decoration  VARCHAR(200),analysisType VARCHAR(200),sampleNumber VARCHAR(200),minimumNumberOfVessels VARCHAR(200),residues VARCHAR(200),notes VARCHAR(1000))";
if (mysqli_query($databaseConnection, $sql)) {
    echo "find table created <br/>";
} else {
    printf("Error creating find table: %s\n", mysqli_error($databaseConnection));
}

?>