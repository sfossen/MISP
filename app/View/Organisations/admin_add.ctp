<div class="users form">
<?php echo $this->Form->create('Organisation');?>
	<fieldset>
		<legend><?php echo __('New Organisation'); ?></legend>
		<p style="font-weight:bold;">If the organisation should have access to this instance, make sure that the Local organisation setting is checked. <br />If you would only like to add a known external organisation for inclusion in sharing groups, uncheck the Local organisation setting.</p>
		<div style="float:left;width:345px;">
			<?php echo $this->Form->input('local', array('label' => 'Local organisation', 'checked' => true));?>
		</div>
		<div class="clear"></div>
		<hr />
		<p style="font-weight:bold;">Mandatory fields.</p>
		<div style="float:left;width:345px;">
		<?php

			echo $this->Form->input('name', array('div' => 'clear', 'style' => 'width:320px;','label' => 'Organisation Identifier', 'placeholder' => 'Brief organisation identifier'));
		?>
		</div>
		<div id="logoDiv" style="margin-top:40px;">No image uploaded for this identifier</div>
		<div class="clear"></div>
		<div style="float:left;width:425px;">
		<?php 
			echo $this->Form->input('uuid', array('div' => 'clear', 'placeholder' => 'Paste UUID or click generate', 'style' => 'width:405px;'));
		?>
		</div>
		<span class="btn btn-inverse" style="margin-top:25px;" onClick="generateOrgUUID();">Generate UUID</span>
		<?php 
			echo $this->Form->input('description', array('label' => 'A brief description of the organisation', 'div' => 'clear', 'class' => 'input-xxlarge', 'type' => 'textarea', 'placeholder' => 'A description of the organisation that is purely informational.'));
		?>
		<hr />
		<p style="font-weight:bold;">The following fields are all optional.</p>
	<?php 
		echo $this->Form->input('nationality', array('options' => $countries));
		echo $this->Form->input('sector', array('placeholder' => 'For example "financial".', 'style' => 'width:300px;'));
		echo $this->Form->input('type', array('class' => 'input-xxlarge', 'label' => 'Type of organisation', 'div' => 'clear', 'placeholder' => 'Freetext description of the org.'));
		echo $this->Form->input('contacts', array('class' => 'input-xxlarge', 'type' => 'textarea', 'div' => 'clear', 'placeholder' => 'You can add some contact details for the organisation here, if applicable.'));
	?>
	</fieldset>
<?php echo $this->Form->button(__('Submit'), array('class' => 'btn btn-primary'));
	echo $this->Form->end();?>
</div>
<?php 
	echo $this->element('side_menu', array('menuList' => 'admin', 'menuItem' => 'addOrg'));
?>
<script type="text/javascript">
	$("#OrganisationName").on('input propertychange paste focusout', function() {
		updateOrgCreateImageField($("#OrganisationName").val());
	});
</script>
