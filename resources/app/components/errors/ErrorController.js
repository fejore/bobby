app.controller('errorCtrl', function($scope, $routeParams, $location) {

  if ($routeParams.code && $routeParams.code == 401) { // Si l'utilisateur CAS n'était pas autorisé à accéder

    $scope.errorCode = 401;
    $scope.errorDesc = "Vous n'êtes pas autorisé à accéder à cette webapp.";

  }
  else if ($routeParams.code && $routeParams.code == 404) {

    $scope.errorCode = 404;
    $scope.errorDesc = "Page demandée introuvable";

  }
  else if ($routeParams.code && $routeParams.code == 500) {

    $scope.errorCode = 500;
    $scope.errorDesc = "Erreur interne, veuillez contacter un administrateur.";

  }
  else {
    $location.path("/");
  }

});
