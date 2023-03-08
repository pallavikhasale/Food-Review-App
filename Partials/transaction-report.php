<?php
$loc = '';
if(isset($_SESSION['report_path'])){
	$loc = $_SESSION['report_path'];
}

?>
<!-- Modal -->
	<div class="modal fade" id="transactionReportModal" role="dialog">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Transaction Report</h4>
				</div>
				<div class="modal-body">
					<?php include $loc; ?>
				</div>
					<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>