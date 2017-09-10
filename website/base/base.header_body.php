<body>
	<div id="container">
		<div id="area_header">
			<?php
				require('./base/base.header.php');
			?>
		</div>
		
		<div id="area_magic_listbox">
			<?php 
				//require('./base/base.magic_listbox.php');
			?>
		</div>
		
		
		<div id="area_toolbar">
			<?php 
				require('./base/toolbar/base.toolbar.php');
			?>
		</div>
		
		
		<div id="dyn_content">
			<?php 
				require('./base/base.load_page.php');
			?>
		</div>
		
		
		<div id="dialog" class="hide">
			
		</div>
		
		
		<div id="area_footer">
			<?php 
				require ('./base/base.footer.php');
			?>
		</div>
		
	</div>
</body>
<?php 
	echo '</html>';
?>