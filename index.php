<?php
/**
 * Entry point of app
 * @package dontu
 */

// Constants definition
define( 'ROOT', __DIR__ . '/' );
define( 'ROOT_URL', 'http://localhost/dontu/' );

/**
 * The auto loading function.
 * Used to autoload Classes as needed by the app.
 *
 * @since   1.0.0
 */
spl_autoload_register( function ( $class_name ) {
	$class_file_name = strtolower( str_replace( '_', '-', $class_name ) ) . '-class';
	require_once ROOT . 'app/' . $class_file_name . '.php';
} );

//$vagrant = new Vagrant( false );
//$results = $vagrant->get_new_data();
//echo '<pre>';
//print_r( $results );
//echo '</pre>';
//die( 'EXIT' );

// Initiate the app.
$app = new Dontu();

/**
 * Server request switch.
 * Passes requests to app based on request type.
 */
if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' && isset( $_GET['post'] ) ) {
	echo $app->do_post_request( $_POST );
	die();
} else if ( isset( $_POST ) && ! empty( $_POST ) &&  isset( $_GET['login'] ) ) {
	$username = $_POST['auth_user'];
	$password = $_POST['auth_pass'];
	$app->login( $username, $password );
	header('Location: '.ROOT_URL);
} else if ( isset( $_GET['post'] ) && isset( $_GET['logout'] ) ) {
	$app->logout( $_GET['logout'] );
	header('Location: '.ROOT_URL);
}

// Set search query if passed by $_GET
if( isset( $_GET['search'] ) && $_GET['search'] != '' ) {
    $search_q = $_GET['search'];
} else {
	$search_q = '';
}
// Retrieve layout and section data
$layout = $app->get_layout_data();
$sections = $app->get_main_sections( $search_q );
//var_dump( $layout );
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>

	<!-- CSS -->
	<link href='//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css' rel='stylesheet' type='text/css'> <!-- Toastr Lib  External style -->
	<link rel="stylesheet" href="css/font-awesome.css"> <!-- Font Awesome style -->
	<link rel="stylesheet" href="css/bootstrap-editable.css"> <!-- Bootstrap Editable style -->
	<link rel="stylesheet" href="js/vendors/wysihtml5/bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.css"> <!-- WYSIHTML Bootstrap Editable style -->
	<link rel="stylesheet" href="js/vendors/wysihtml5/bootstrap-wysihtml5-0.0.2/wysiwyg-color.css"> <!-- WYSIHTML Bootstrap Editable style -->
	<link rel="stylesheet" href="css/bootstrap-darkly.css"> <!-- Bootstrap Darkly Theme style -->
	<link rel="stylesheet" href="css/reset.css"> <!-- CSS reset -->
	<link rel="stylesheet" href="css/style.css"> <!-- Resource style -->

	<!-- Javascript Vendors -->
	<script src="js/modernizr.js"></script> <!-- Modernizr -->
	<script src="js/jquery-2.1.1.js"></script>
	<script src="js/jquery.mobile.custom.min.js"></script>
	<script src="js/main.js"></script> <!-- Resource jQuery -->
	<script src="js/bootstrap.js"></script> <!-- Bootstrap -->
	<script src="js/bootstrap-editable.js"></script> <!-- Bootstrap Editable -->
	<script src="js/vendors/wysihtml5/bootstrap-wysihtml5-0.0.2/wysihtml5-0.3.0.js"></script> <!-- WYSIHTML Editable -->
	<script src="js/vendors/wysihtml5/bootstrap-wysihtml5-0.0.2/bootstrap-wysihtml5-0.0.2.js"></script> <!-- WYSIHTML Bootstrap Editable -->
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script> <!-- Toastr Lib  External -->
	<script src="js/Chart.js"></script> <!-- Chart.js Library -->

	<script src="js/dontu.js"></script> <!-- Andrei Dontu Scripts -->
	<title>Andrei Dontu | Web</title>
</head>
<body>
	<nav class="navbar navbar-default navbar-fixed-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
                    <?php
                    foreach ( $sections as $section ) {
                    ?>
                    <li>
                        <a id="section_link_<?php echo $section['id'] ?>" href="#<?php echo $section['name'] ?>"><span id="section_name_<?php echo $section['id'] ?>" ><?php echo $section['value'] ?></span>
                        <?php if ( $app->is_logged() ) { ?>
                            <span class="btn-group" style="position: relative; display: inline-block; width: 100px; padding-left: 10px;" >
                                <button class="btn btn-sm btn-primary" onclick="return editSection( $(this), <?php echo $section['id'] ?>);"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" onclick="return deleteSection(<?php echo $section['id'] ?>);"><i class="fa fa-trash-o"></i></button>
                            </span>
                        <?php } ?>
                        </a>
                    </li>
                    <?php } ?>

                    <?php if ( $app->is_logged() ) { ?>
                        <li><a href="?post&logout=<?php echo $app->username; ?>"><i class="fa fa-lock"></i> Log out</a></li>
                    <?php } else { ?>
                        <li><a href="#" data-toggle="modal" data-target="#loginModal"><i class="fa fa-user-circle-o"></i> Log in</a></li>
                    <?php } ?>
                </ul>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<form class="navbar-form navbar-right" method="get" action="?query">
					<div class="form-group">
						<div class="input-group">
							<input type="text" name="search" class="form-control input-sm" placeholder="Cauta ..." value="<?php echo $search_q; ?>">
							<span class="input-group-btn">
								<button type="submit" class="btn btn-default btn-sm"><i class="fa fa-search"></i></button>
							</span>
						</div>
					</div>
				</form>
			</div>
		</div>
	</nav>
	<div class="jumbotron">
        <header>
            <h1><span id="main-title"><?php echo ( isset( $layout['edit-title'] ) )?$layout['edit-title']['value']:'Andrei Dontu'; ?></span>
                <?php if ( $app->is_logged() ) { ?>
                    <span class="btn-group inline"><button id="edit-main-title" data-pk="<?php echo ( isset( $layout['edit-title'] ) )?$layout['edit-title']['id']:0; ?>" class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i></button></span>
                <?php } ?>
            </h1>
            <h2><span id="sub-title"><?php echo ( isset( $layout['edit-sub-title'] ) )?$layout['edit-sub-title']['value']:'Titlu Licenta'; ?></span>
                <?php if ( $app->is_logged() ) { ?>
                    <span class="btn-group inline"><button id="edit-sub-title" data-pk="<?php echo ( isset( $layout['edit-sub-title'] ) )?$layout['edit-sub-title']['id']:0; ?>" class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i></button></span>
                <?php } ?>
            </h2>
        </header>
	</div>

	<section class="cd-faq">
        <?php if ( $app->is_logged() ) { ?>
		<ul class="cd-faq-categories">
			<li class="new-section"><a href="#" id="new-section-name"><i class="fa fa-plus-circle"></i> Adauga Sectiune</a></li>
		</ul> <!-- cd-faq-categories -->
        <?php } ?>

		<div class="cd-faq-items <?php if ( $app->is_logged() ) { echo 'has-padding';  }?>">

            <ul id="charts-data" class="cd-faq-group">
                <li style="position: relative" class="content-visible">
                    <a class="cd-faq-trigger" href="#0"><span id="charts_title">Chart</span></a>
                    <div class="cd-faq-content" id="chart_content" style="display: block;">
                        <h3>Last Read</h3>
                        <span style="display: block; width: 900px; margin: auto;">
                            <canvas id="cpuChart" width="300" height="300" style="display: block; float: left;"></canvas>
                            <canvas id="memChart" width="300" height="300" style="display: block; float: left;"></canvas>
                            <canvas id="diskChart" width="300" height="300" style="display: block; float: left;"></canvas>
                        </span>
                        <span>
                            <hr style="clear: both;" />
                        </span>
                        <h3>History</h3>
                        <span style="display: block; width: 900px; margin: auto;">
                            <canvas id="history-cpu" width="900" height="300" style="display: block;"></canvas>
                            <canvas id="history-mem" width="900" height="300" style="display: block;"></canvas>
                            <canvas id="history-disk" width="900" height="300" style="display: block;"></canvas>
                        </span>
                        <span>
                            <hr style="clear: both;" />
                        </span>
                        <button type="button" class="btn btn-success btn-lg btn-block" onclick="return getHistoryData();"><span class="fa fa-chevron-circle-up"></span> Boot VM's & Get Data </button>
                        <?php
                        $history_data = $app->get_history();
                        $display_history = array();
                        $display_history[0] = array(
                                'read_date' => date( 'Y-m-d H:i:s' ),
                                'cpu_win' => 0,
                                'cpu_ubt' => 0,
                                'mem_win' => 0,
                                'mem_ubt' => 0,
                                'dsk_win' => 0,
                                'dsk_ubt' => 0
                        );
                        $i = 0;
                        foreach ( $history_data as $row ) {
	                        $display_history[$i] = array(
		                        'read_date' => $row['read_date'],
		                        'cpu_win' => $row['cpu_win'],
		                        'cpu_ubt' => $row['cpu_ubt'],
		                        'mem_win' => $row['mem_win'],
		                        'mem_ubt' => $row['mem_ubt'],
		                        'dsk_win' => $row['dsk_win'],
		                        'dsk_ubt' => $row['dsk_ubt']
                            );
	                        $i++;
                        }

                        $dataSetNowCPU = '['.$display_history[0]['cpu_win'].','.$display_history[0]['cpu_ubt'].']';
                        $dataSetNowMEM = '['.$display_history[0]['mem_win'].','.$display_history[0]['mem_ubt'].']';
                        $dataSetNowDSK = '['.$display_history[0]['dsk_win'].','.$display_history[0]['dsk_ubt'].']';

                        $dataSetHistoryLabels = '[';

                        $dataSetHistoryCPU_win = '[';
                        $dataSetHistoryCPU_ubt = '[';
                        $dataSetHistoryMEM_win = '[';
                        $dataSetHistoryMEM_ubt = '[';
                        $dataSetHistoryDSK_win = '[';
                        $dataSetHistoryDSK_ubt = '[';
                        if( sizeof( $display_history ) > 0 ) {
	                        for ( $j = sizeof( $display_history ) - 1; $j >= 0; $j-- ) {
		                        $dataSetHistoryLabels .= '"' . date( 'M, d H:i:s', strtotime( $display_history[$j]['read_date'] ) ) . '"';

		                        $dataSetHistoryCPU_win .= $display_history[$j]['cpu_win'];
		                        $dataSetHistoryCPU_ubt .= $display_history[$j]['cpu_ubt'];
		                        $dataSetHistoryMEM_win .= $display_history[$j]['mem_win'];
		                        $dataSetHistoryMEM_ubt .= $display_history[$j]['mem_ubt'];
		                        $dataSetHistoryDSK_win .= $display_history[$j]['dsk_win'];
		                        $dataSetHistoryDSK_ubt .= $display_history[$j]['dsk_ubt'];
		                        if( $j > 0 ) {
			                        $dataSetHistoryLabels .= ',';
			                        $dataSetHistoryCPU_win .= ',';
			                        $dataSetHistoryCPU_ubt .= ',';
			                        $dataSetHistoryMEM_win .= ',';
			                        $dataSetHistoryMEM_ubt .= ',';
			                        $dataSetHistoryDSK_win .= ',';
			                        $dataSetHistoryDSK_ubt .= ',';
		                        }
	                        }
                        }
                        $dataSetHistoryLabels .= ']';
                        $dataSetHistoryCPU_win .= ']';
                        $dataSetHistoryCPU_ubt .= ']';
                        $dataSetHistoryMEM_win .= ']';
                        $dataSetHistoryMEM_ubt .= ']';
                        $dataSetHistoryDSK_win .= ']';
                        $dataSetHistoryDSK_ubt .= ']';
                        ?>
                        <script>
                            var cth_cpu = document.getElementById("history-cpu");
                            var cth_mem = document.getElementById("history-mem");
                            var cth_dsk = document.getElementById("history-disk");
                            var ctx = document.getElementById("cpuChart");
                            var cty = document.getElementById("memChart");
                            var ctz = document.getElementById("diskChart");

                            var historyLabels = <?php echo $dataSetHistoryLabels ?>;
                            var options = {
                                responsive: false,
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero:true
                                        }
                                    }]
                                }
                            };

                            var dataSetNowCPU = <?php echo $dataSetNowCPU ?>;
                            var dataSetNowMEM = <?php echo $dataSetNowMEM ?>;
                            var dataSetNowDSK = <?php echo $dataSetNowDSK ?>;

                            var dataSetHistoryCPU_win = <?php echo $dataSetHistoryCPU_win ?>;
                            var dataSetHistoryCPU_ubnt = <?php echo $dataSetHistoryCPU_ubt ?>;

                            var dataSetHistoryMEM_win = <?php echo $dataSetHistoryMEM_win ?>;
                            var dataSetHistoryMEM_ubnt = <?php echo $dataSetHistoryMEM_ubt ?>;

                            var dataSetHistoryDSK_win = <?php echo $dataSetHistoryDSK_win ?>;
                            var dataSetHistoryDSK_ubnt = <?php echo $dataSetHistoryDSK_ubt ?>;

                            var historyChartCPU = new Chart(cth_cpu, {
                                type: 'line',
                                data: {
                                    labels: historyLabels,
                                    datasets: [
                                        {
                                            label: 'CPU-Win',
                                            data: dataSetHistoryCPU_win,
                                            backgroundColor: [
                                                'rgba(54, 162, 235, 0.2)'
                                            ],
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'CPU-Ubt',
                                            data: dataSetHistoryCPU_ubnt,
                                            backgroundColor: [
                                                'rgba(255, 159, 64, 0.2)'
                                            ],
                                            borderColor: [
                                                'rgba(255, 159, 64, 1)'
                                            ],
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: options
                            });

                            var historyChartMEM = new Chart(cth_mem, {
                                type: 'line',
                                data: {
                                    labels: historyLabels,
                                    datasets: [
                                        {
                                            label: 'MEM-Win',
                                            data: dataSetHistoryMEM_win,
                                            backgroundColor: [
                                                'rgba(54, 162, 235, 0.2)'
                                            ],
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'MEM-Ubt',
                                            data: dataSetHistoryMEM_ubnt,
                                            backgroundColor: [
                                                'rgba(255, 159, 64, 0.2)'
                                            ],
                                            borderColor: [
                                                'rgba(255, 159, 64, 1)'
                                            ],
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: options
                            });

                            var historyChartDSK = new Chart(cth_dsk, {
                                type: 'line',
                                data: {
                                    labels: historyLabels,
                                    datasets: [
                                        {
                                            label: 'DSK-Win',
                                            data: dataSetHistoryDSK_win,
                                            backgroundColor: [
                                                'rgba(54, 162, 235, 0.2)'
                                            ],
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1
                                        },
                                        {
                                            label: 'DSK-Ubt',
                                            data: dataSetHistoryDSK_ubnt,
                                            backgroundColor: [
                                                'rgba(255, 159, 64, 0.2)'
                                            ],
                                            borderColor: [
                                                'rgba(255, 159, 64, 1)'
                                            ],
                                            borderWidth: 1
                                        }
                                    ]
                                },
                                options: options
                            });

                            var cpuChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: ["Windows", "Ubuntu"],
                                    datasets: [{
                                        label: 'CPU',
                                        data: dataSetNowCPU,
                                        backgroundColor: [
                                            'rgba(54, 162, 235, 0.2)',
                                            'rgba(255, 159, 64, 0.2)'
                                        ],
                                        borderColor: [
                                            'rgba(54, 162, 235, 1)',
                                            'rgba(255, 159, 64, 1)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: options
                            });

                            var memChart = new Chart(cty, {
                                type: 'bar',
                                data: {
                                    labels: ["Windows", "Ubuntu"],
                                    datasets: [{
                                        label: 'Memory',
                                        data: dataSetNowMEM,
                                        backgroundColor: [
                                            'rgba(54, 162, 235, 0.2)',
                                            'rgba(255, 159, 64, 0.2)'
                                        ],
                                        borderColor: [
                                            'rgba(54, 162, 235, 1)',
                                            'rgba(255, 159, 64, 1)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: options
                            });

                            var diskChart = new Chart(ctz, {
                                type: 'bar',
                                data: {
                                    labels: ["Windows", "Ubuntu"],
                                    datasets: [{
                                        label: 'Disk Usage',
                                        data: dataSetNowDSK,
                                        backgroundColor: [
                                            'rgba(54, 162, 235, 0.2)',
                                            'rgba(255, 159, 64, 0.2)'
                                        ],
                                        borderColor: [
                                            'rgba(54, 162, 235, 1)',
                                            'rgba(255, 159, 64, 1)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: options
                            });
                        </script>
                    </div> <!-- cd-faq-content -->
                </li>
            </ul>

			<?php
			if( isset( $search_q ) && empty( $sections ) ) {
                echo '<div class="alert alert-warning" role="alert"><i class="fa fa-info-circle"></i> Nu am gasit informatia cautata. Va rugam sa reincercati!</div>';
			}

			foreach ( $sections as $section ) {
			?>
			<ul id="<?php echo $section['name'] ?>" class="cd-faq-group">
				<li class="cd-faq-title"><h2><?php echo $section['value'] ?></h2></li>
                <?php
                $subsections = $app->get_sub_sections( $section['id'], $search_q );
                foreach ( $subsections as $sub ) {
                ?>
				<li style="position: relative">
                    <a class="cd-faq-trigger" href="#0"><span id="sub_section_title_<?php echo $sub['id'] ?>"><?php echo $sub['title'] ?></span></a>
					<div class="cd-faq-content" id="sub_section_content_<?php echo $sub['id'] ?>">
						<?php echo $sub['value'] ?>
					</div> <!-- cd-faq-content -->
					<?php if ( $app->is_logged() ) { ?>
                        <span class="btn-group" style="position: absolute; top: 0; right: 60px;">
							<button class="btn btn-sm btn-primary" onclick="return editSubSection( $(this), <?php echo $sub['id'] ?>, <?php echo $section['id']; ?> );"><i class="fa fa-pencil"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="return deleteSubSection( <?php echo $sub['id'] ?> );"><i class="fa fa-trash-o"></i></button>
						</span>
					<?php } ?>
				</li>
                <?php } ?>

				<?php if ( $app->is_logged() ) { ?>
				<li class="cd-faq-title"><button type="button" class="btn btn-success btn-lg btn-block" onclick="return newSubElement( $(this), <?php echo $section['id'] ?>);"><i class="fa fa-plus-circle"></i> Adauga</button></li>
				<?php } ?>
			</ul> <!-- cd-faq-group -->
			<?php
			}
			?>
		</div> <!-- cd-faq-items -->
		<a href="#0" class="cd-close-panel">Close</a>
	</section> <!-- cd-faq -->

	<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<form method="post" action="?login">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="loginModalLabel"><i class="fa fa-user-circle-o"></i> Log in | Andrei Dontu</h4>
					</div>
					<div class="modal-body">
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-user-circle-o"></i></div>
									<input name="auth_user" type="text" class="form-control" id="usernameInput" placeholder="User Name">
								</div>
								<br/>
								<div class="input-group">
									<div class="input-group-addon"><i class="fa fa-lock"></i></div>
									<input name="auth_pass" type="password" class="form-control" id="passwordInput" placeholder="Password">
								</div>
							</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-ban"></i> Anuleaza</button>
						<button type="submit" class="btn btn-success"><i class="fa fa-user-circle-o"></i> Log in</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
