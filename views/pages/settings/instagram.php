<?php if (isset($errors)): ?>
<div class="alert-message red">
	<p><strong><?php echo __("Error"); ?></strong></p>
	<ul>
		<?php if (is_array($errors)): ?>
			<?php foreach ($errors as $error): ?>
				<li><?php echo $error; ?></li>
			<?php endforeach; ?>
		<?php else: ?>
			<li><?php echo $errors; ?></li>
		<?php endif; ?>
	</ul>
</div>
<?php endif; ?>

<?php if (isset($success)): ?>
	<div class="alert-message blue">
	<p><strong><?php echo __("Success"); ?></strong></p>
	<ul>
		<li><?php echo __('Successfully Registered SwiftRiver on Instagram'); ?></li>
	</ul>
	</div>
<?php endif; ?>

<?php echo Form::open(); ?>
<article class="container base">
	<header class="container cf">
		<div class="property-title">
			<h1><?php echo __("Step 1: Register A Client at Instagram"); ?></h1>
		</div>
	</header>

	<section class="property-parameters">
		<div class="parameter">
			<label for="callback_url">
				<p class="field"><?php echo __("Callback URL:"); ?></p>
				<input type="text" value="<?php echo $callback_url; ?>" name="callback_url" id="callback_url" disabled="disabled" />
				<p class="button-blue button-small generate" style="float: right;">
					<a href="http://instagram.com/developer/clients/register/" target="_blank" title="Go To Instagram" >Go To Instagram</a>
				</p>
			</label>
		</div>
	</section>
</article>

<article class="container base">
	<header class="container cf">
		<div class="property-title">
			<h1><?php echo __("Step 2: Get a Token with Your Registered Client Information"); ?></h1>
		</div>
	</header>

	<section class="property-parameters">
		<div class="parameter">
			<label for="client_id">
				<p class="field"><?php echo __("Client ID:"); ?></p>
				<?php echo Form::input('instagram_client_id', $settings['instagram_client_id']); ?>
			</label>
		</div>

		<div class="parameter">
			<label for="client_secret">
				<p class="field"><?php echo __("Client Secret:"); ?></p>
				<?php echo Form::input('instagram_client_secret', $settings['instagram_client_secret']); ?>
			</label>
		</div>
	</section>
</article>

<div class="save-toolbar">
	<p class="button-blue"><a href="#" onclick="submitForm(this)"><?php echo __("Retrieve Token"); ?></a></p>
</div>
<?php echo Form::close(); ?>