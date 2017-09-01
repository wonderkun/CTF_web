<html ng-app>
<head>
<meta charset=utf-8>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.5/angular.js"></script>
</head>
<body>
<input id="username" name="username" tabindex="1" ng-model="username" ng-init="username='<?php if(strlen($_GET["username"])<37){echo htmlspecialchars($_GET["username"]);}?>'" placeholder="username" maxlength="11" type="text">
</body>
</html>
