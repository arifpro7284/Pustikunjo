<div class='wbfb_masonry_container'>
	<?php
	foreach ( $args['posts'] as $post ) :
		if ( empty( $post->picture ) && empty( $post->full_picture ) ) {
			continue;
		}
		?>
		<div class="wbfb_masonry_post" style="width: <?php echo esc_attr( $args['masonry_post_width'] ) ?>px !important;">
			<?php if ( $args['show_feed_header'] ): ?>
				<div class="wbfb_masonry_post_header">
					<div class="wbfb_profile_pic">
						<img src="<?php echo esc_url( $account->avatar ) ?>" alt="" width="50" height="50" style="border-radius: 50px">
					</div>
					<div class="wbfb_profile_data">
						<div class="wbfb_profile_data_name">
							<a href="https://facebook.com/<?php echo esc_attr( $account->id ) ?>" target="_blank">
								<?php echo esc_html( $account->name ) ?>
							</a>
						</div>
						<div class="wbfb_post_data"><?php echo esc_html( time_elapsed_string( $post->created_time ) ) ?></div>
					</div>
				</div>
			<?php endif; ?>
			<div class="wbfb_masonry_post_body">
				<?php if ( ! empty( $post->attachments ) ) : ?>
					<?php foreach ( $post->attachments as $attachment ) : ?>
						<a <?php echo 'fb_link' == $args['fbimages_link'] ? "href='" . esc_url( "https://facebook.com/$post->id" ) . "' target='_blank'" : '' ?>
								data-remodal-target="<?php echo esc_attr( $post->id ) ?>" class="wbfb_popup_link">
							<div class="wbfb_masonry_post_attachment">
								<?php if ( isset( $post->shared_post ) ): ?>
									<a href="<?php echo esc_url( "https://facebook.com/" . $post->shared_post->id ) ?>">
										<div class="wbfb_masonry_repost">
											<?php if ( ! empty( $post->shared_post->picture ) ): ?>
												<div class="wbfb_repost_picture">
													<img src="<?php echo esc_url( $post->shared_post->picture ) ?>" alt="">
												</div>
											<?php endif; ?>
											<?php if ( ! empty( $post->shared_post->message ) ): ?>
												<div class="wbfb_repost_text">
													<?php echo esc_html( $post->shared_post->message ) ?>
												</div>
											<?php endif; ?>
										</div>
									</a>
								<?php elseif ( 'photo' === $attachment->type || 'gallery' === $attachment->type ): ?>
									<img src="<?php echo esc_url( $post->full_picture ) ?>" alt="">
								<?php elseif ( 'video_inline' === $attachment->type || 'video_autoplay' === $attachment->type ): ?>
									<img src="<?php echo esc_url( $attachment->media->image->src ) ?>" alt="">
								<?php else: ?>
									<img src="<?php echo esc_url( $attachment->media->image->src ?? $post->full_picture ) ?>" alt="">
								<?php endif; ?>
							</div>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>

			</div>
			<div class="wbfb_masonry_post_footer">
				<div class="wbfb_masonry_post_share">
					<a href="https://facebook.com/<?php echo esc_attr( $post->id ) ?>" target="_blank">View on Facebook</a> |
					<a href="">Share</a>
				</div>
				<div class="wbfb_masonry_post_stats">
					<ul class="wfb-meta wfb-light">
						<li class="wfb-likes">
				            <span class="wfb-icon wfb-like">
					        <svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Like" alt="Like"
					             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path ...></path></svg>
						<span class="wfb-count"><?php echo esc_html( $post->likes_count ) ?></span>
						</li>
						<li class="wfb-shares">
				            <span class="wfb-icon wfb-share">
					        <svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Share" alt="Share"
					             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path ...></path></svg>
                        <span class="wfb-count"><?php echo esc_html( $post->shares_count ) ?></span>
						</li>
						<li class="wfb-comments">
				            <span class="wfb-icon wfb-comment">
					        <svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Comment"
					             alt="Comment"
					             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                <path ...></path></svg>
                                <span class="wfb-count"><?php echo esc_html( $post->comments_count ) ?></span>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
		if ( $i >= $args['images_number'] ) {
			break;
		}
		$i ++;
	endforeach; ?>
</div>