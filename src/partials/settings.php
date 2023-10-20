<div class="wrap">

	<h1><?php echo __( 'Setting', 'il-checker' ); ?></h1>

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><?php echo __( 'Check for links to homepage on other page', 'il-checker' ); ?></th>
			<td>
				<form action="" name="il-checker-submit-check" method="POST">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'il-checker' ); ?>">
					<?php submit_button( 'Start Link Check', 'primary', 'il-checker-submit' ); ?>
				</form>
			</td>
		</tr>
		</tbody>
	</table>

	<?php if ( ! empty( $homepage_links ) ) : ?>
		<h1 style="margin-top: 25px;"><?php echo __( 'Homepage internal links', 'il-checker' ); ?></h1>
		<h4><?php echo __( 'All internal links from homepage to your internal pages', 'il-checker' ); ?></h4>
		<table class="form-table">
			<thead>
			<tr>
				<th><?php echo __( 'S.No.', 'il-checker' ); ?></th>
				<th><?php echo __( 'Linked Page', 'il-checker' ); ?></th>
				<th><?php echo __( 'Anchor Text', 'il-checker' ); ?></th>
				<th><?php echo __( 'Last Checked', 'il-checker' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $homepage_links as $key => $homepage_link ) : ?>
				<tr>
					<td><?php echo $key + 1; ?></td>
					<td><a target="_blank"
							href="<?php echo $homepage_link['link']; ?>"><?php echo $homepage_link['link']; ?></a></td>
					<td><?php echo $homepage_link['anchor']; ?></td>
					<td><?php echo $homepage_link['last_checked']; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>


	<?php if ( ! empty( $link_results ) ) : ?>
		<h1 style="margin-top: 25px;"><?php echo __( 'Link status', 'il-checker' ); ?></h1>
		<h4><?php echo __( 'Your links from other page to homepage', 'il-checker' ); ?></h4>
		<table class="form-table">
			<thead>
			<tr>
				<th><?php echo __( 'S.No.', 'il-checker' ); ?></th>
				<th><?php echo __( 'Linked Page', 'il-checker' ); ?></th>
				<th><?php echo __( 'Anchor Text', 'il-checker' ); ?></th>
				<th><?php echo __( 'Linked To', 'il-checker' ); ?></th>
				<th><?php echo __( 'Last Checked', 'il-checker' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $link_results as $key => $link_result ) : ?>
				<tr>
					<td><?php echo $key + 1; ?></td>
					<td><a target="_blank"
							href="<?php echo $link_result['page_link']; ?>"><?php echo $link_result['page_link']; ?></a>
					</td>
					<td><?php echo $link_result['anchor_text']; ?></td>
					<td><?php echo $link_result['linked_to']; ?></td>
					<td><?php echo $link_result['last_checked']; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

</div>
