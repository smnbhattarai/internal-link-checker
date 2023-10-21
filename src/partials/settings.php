<div class="wrap">

	<h1><?php echo esc_html__( 'Setting', 'il-checker' ); ?></h1>

	<table class="form-table" role="presentation">
		<tbody>
		<tr>
			<th scope="row"><?php echo esc_html__( 'Check for links to homepage on other page', 'il-checker' ); ?></th>
			<td>
				<form action="" name="il-checker-submit-check" method="POST">
					<input type="hidden" name="_wpnonce" value="<?php echo esc_html( wp_create_nonce( 'il-checker' ) ); ?>">
					<?php submit_button( 'Start Link Check', 'primary', 'il-checker-submit' ); ?>
				</form>
			</td>
		</tr>
		</tbody>
	</table>

	<?php if ( ! empty( $il_checker_homepage_links ) ) : ?>
		<h1 style="margin-top: 25px;"><?php echo esc_html__( 'Homepage internal links', 'il-checker' ); ?></h1>
		<h4><?php echo esc_html__( 'All internal links from homepage to your internal pages', 'il-checker' ); ?></h4>
		<table class="form-table">
			<thead>
			<tr>
				<th><?php echo esc_html__( 'S.No.', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Linked Page', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Anchor Text', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Last Checked', 'il-checker' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $il_checker_homepage_links as $il_checker_key => $il_checker_homepage_link ) : ?>
				<tr>
					<td><?php echo esc_html( $il_checker_key + 1 ); ?></td>
					<td><a target="_blank" href="<?php echo esc_html( $il_checker_homepage_link['link'] ); ?>"><?php echo esc_html( $il_checker_homepage_link['link'] ); ?></a>
					</td>
					<td><?php echo esc_html( $il_checker_homepage_link['anchor'] ); ?></td>
					<td><?php echo esc_html( $il_checker_homepage_link['last_checked'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>


	<?php if ( ! empty( $il_checker_link_results ) ) : ?>
		<h1 style="margin-top: 25px;"><?php echo esc_html__( 'Link status', 'il-checker' ); ?></h1>
		<h4><?php echo esc_html__( 'Your links from other page to homepage', 'il-checker' ); ?></h4>
		<table class="form-table">
			<thead>
			<tr>
				<th><?php echo esc_html__( 'S.No.', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Linked Page', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Anchor Text', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Linked To', 'il-checker' ); ?></th>
				<th><?php echo esc_html__( 'Last Checked', 'il-checker' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $il_checker_link_results as $il_checker_key => $il_checker_link_result ) : ?>
				<tr>
					<td><?php echo esc_html( $il_checker_key + 1 ); ?></td>
					<td><a target="_blank" href="<?php echo esc_url( $il_checker_link_result['page_link'] ); ?>"><?php echo esc_url( $il_checker_link_result['page_link'] ); ?></a>
					</td>
					<td><?php echo esc_html( $il_checker_link_result['anchor_text'] ); ?></td>
					<td><?php echo esc_url( $il_checker_link_result['linked_to'] ); ?></td>
					<td><?php echo esc_html( $il_checker_link_result['last_checked'] ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

</div>
