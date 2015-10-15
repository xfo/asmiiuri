var cncToolMonitor = angular.module('cncToolMonitor', ['ngRoute', 'ui.bootstrap', 'highcharts-ng']);

cncToolMonitor.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
  $routeProvider
    .when('/', {
      templateUrl: "templates/home.html",
      controller: "HomeController"
    })
    .when('/add_new_tool', {
      templateUrl: "templates/add_new_tool.html",
      controller: "NewToolController"
    })
    .when('/tool_detail/:tool_id', {
      templateUrl: "templates/tool_detail.html",
      controller: "ToolDetailController"
    })
    .when('/tool_work/:tool_id', {
      templateUrl: "templates/tool_work.html",
      controller: "ToolWorkController"
    })
    .when('/tool_work_wear/:tool_id', {
      templateUrl: "templates/tool_work_wear.html",
      controller: "ToolWorkWear"
    })
    .when('/add_new_revision/:tool_id', {
      templateUrl: "templates/add_new_revision.html",
      controller: "NewRevisionController"
    }).otherwise({
      redirectTo: '/'
    });

  $locationProvider
    .html5Mode(true);
}]);

cncToolMonitor

  .controller("NewToolController", ['$scope', '$http', '$location', function($scope, $http, $location) {
  $http.get("/api.php?action=GetAllInformationToAddNew")
    .success(function(data) {
      $scope.instrument_types = data.types;
      $scope.instrument_manufacturers = data.manufacturers;
      $scope.instrument_suppliers = data.suppliers;
      $scope.instrument_matherials = [
        "быстрорежущая сталь",
        "твердый сплав: вольфрамовый",
        "твердый сплав: танталовольфрамовый",
        "твердый сплав: титанотанталовольфрамовый",
        "твердый сплав: безвольфрамовый",
        "керамика"
      ];
    });

  $scope.addNewInstrument = function() {
    $http({
      method: 'POST',
      url: "/api.php?action=AddNewInstrument",
      data: 'instrument_name=' + $scope.instrument_name +
        '&instrument_type=' + $scope.instrument_type +
        '&instrument_material=' + $scope.instrument_material +
        '&instrument_manufacturer=' + $scope.instrument_manufacturer +
        '&instrument_supplier=' + $scope.instrument_supplier +
        '&holding_method=' + $scope.holding_method +
        '&shape_type=' + $scope.shape_type +
        '&holder_type=' + $scope.holder_type +
        '&back_angle=' + $scope.back_angle +
        '&feed_direction=' + $scope.feed_direction +
        '&holder_heigth=' + $scope.holder_heigth +
        '&holder_width=' + $scope.holder_width +
        '&tool_length=' + $scope.tool_length +
        '&blade_edge_length=' + $scope.blade_edge_length +
        '&max_back_edge_wear=' + $scope.max_back_edge_wear +
        '&max_front_edge_wear_hl=' + $scope.max_front_edge_wear_hl +
        '&max_front_edge_wear_ll=' + $scope.max_front_edge_wear_ll +
        '&max_radius_edge_wear=' + $scope.max_radius_edge_wear +
        '&max_length_wear=' + $scope.max_length_wear,
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    }).success(function(data) {
      window.location.href = "/";
    });
  };
}])

.controller("HomeController", ['$scope', '$http', '$location', function($scope, $http, $location) {
  $http.get("/api.php?action=GetAllTools")
    .success(function(data) {
      $scope.cnc_tools = data;
    });
}])

.controller("AppUserController", ['$scope', function($scope) {
  $scope.user = "Харисов Рамиль Азатович";
}])

.controller("ToolDetailController", ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;
  $http.get("/api.php?action=GetOneTool&tool_id=" + $scope.tool_id)
    .success(function(data) {
      $scope.tool_details = data[0];
    });
  $http.get("/api.php?action=ShowAllRevisions&tool_id=" + $scope.tool_id)
    .success(function(data) {
      $scope.revisions = data;
    });
}])

.controller("NewRevisionController", ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;
  $scope.addNewRevision = function() {
    $http({
      method: 'POST',
      url: "/api.php?action=AddNewRevision&tool_id=" + $scope.tool_id,
      data: 'box_stock_num=' + $scope.box_stock_num +
        '&parameter_value=' + $scope.parameter_value,

      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    }).success(function(data) {
      alert("Добавлено!");
    });
  };

  $scope.addNewWork = function() {
    $http({
      method: 'POST',
      url: "/api.php?action=AddNewToolWork&tool_id=" + $scope.tool_id,
      data: 's=' + $scope.s +
        '&r=' + $scope.r,

      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      }
    }).success(function(data) {
      alert("Добавлено!");
    });
  };

}])

.controller('toolWearChronology', ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;
  $http.get("/api.php?action=GetToolWearHistory&tool_id=" + $scope.tool_id + "&tool_parameter_id=1")
    .success(function(data) {
      var tool_wear_history = data;
      $scope.highchartsNG = {
        options: {
          chart: {
            type: 'areaspline'
          },
          legend: {
            enabled: false
          },
          credits: {
            enabled: false
          },
          plotOptions: {
            areaspline: {
              fillOpacity: 0.1
            },
            series: {
              //color: '#000000',
              fillOpacity: 0.1,
              fillColor: {
                linearGradient: [0, 0, 0, 200],
                stops: [
                  [0, '#8cc6ff'],
                  [1, '#fff']
                ]
              }
            }
          }
        },
        title: {
          text: 'Хронология износа режущего инструмента'
        },
        xAxis: {
          categories: tool_wear_history["dates"],
          title: {
            text: 'Дата проведения ревизии'
          }
        },
        yAxis: {
          title: {
            text: '% Износа инструмента'
          }
        },
        series: [{
          name: '% Износа',
          data: tool_wear_history["values"]
        }]
      }
    });
}])

.controller("ToolResourceController", ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;

  $http.get("/api.php?action=GetToolResource&tool_id=" + $scope.tool_id)
    .success(function(data) {
      $scope.tool_resource = data;
    });
}])

.controller("ToolWorkController", ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;

  $http.get("/api.php?action=GetToolWorkHistory&tool_id=" + $scope.tool_id)
    .success(function(data) {
      $scope.tool_work = data;
      $scope.tool_work_chart = {
        options: {
          chart: {
            type: 'areaspline'
          },
          legend: {
            enabled: false
          },
          credits: {
            enabled: false
          },
          plotOptions: {
            areaspline: {
              fillOpacity: 0.1
            },
            series: {
              //color: '#000000',
              fillOpacity: 0.1,
              fillColor: {
                linearGradient: [0, 0, 0, 200],
                stops: [
                  [0, '#8cc6ff'],
                  [1, '#fff']
                ]
              }
            }
          }
        },
        title: {
          text: 'Хронология механической работы режущего инструмента'
        },
        xAxis: {
          categories: $scope.tool_work["chart"]["dates"],
          title: {
            text: 'Дата'
          }
        },
        yAxis: {
          title: {
            text: 'Дж'
          }
        },
        series: [{
          name: 'Дж',
          data: $scope.tool_work["chart"]["values"]
        }]
      }
    });
}])

.controller("ToolWorkWear", ['$scope', '$http', '$location', '$routeParams', function($scope, $http, $location, $routeParams) {
  $scope.tool_id = $routeParams.tool_id;

  $http.get("/api.php?action=GetToolWorkWearRelation&tool_id=" + $scope.tool_id)
    .success(function(data) {
      $scope.tool_work_wear = data;
      $scope.tool_work_wear_chart = {
        options: {
          chart: {
            type: 'areaspline'
          },
          legend: {
            enabled: false
          },
          credits: {
            enabled: false
          },
          plotOptions: {
            areaspline: {
              fillOpacity: 0.1
            },
            series: {
              //color: '#000000',
              fillOpacity: 0.1,
              fillColor: {
                linearGradient: [0, 0, 0, 200],
                stops: [
                  [0, '#8cc6ff'],
                  [1, '#fff']
                ]
              }
            }
          }
        },
        title: {
          text: 'Зависимость износа инструмента от совершенной мех. работы'
        },
        xAxis: {
          categories: $scope.tool_work_wear["work"],
          title: {
            text: 'Дж'
          }
        },
        yAxis: {
          title: {
            text: '% износа инструмента'
          }
        },
        series: [{
          name: '%',
          data: $scope.tool_work_wear["wear"]
        }]
      }
    });
}])
