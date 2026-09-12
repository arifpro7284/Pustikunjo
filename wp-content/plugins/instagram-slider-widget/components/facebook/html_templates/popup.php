<?php
/** @var WIS\Facebook\Includes\Api\FacebookAccount $account */
$account = $args['account'];
?>
<?php
/** @var \WIS\Facebook\Includes\Api\WFB_Facebook_Post $post */
foreach ( $args['posts'] as $post ): ?>
	<div class="remodal" data-remodal-id="<?php echo esc_attr( $post->id ); ?>">
		<div class="wfb-remodal-container">
			<div class="wfb-remodal-pic">
				<img src="<?php echo esc_url( $post->full_picture ); ?>" alt="">
			</div>
			<div class="wfb-remodal-data">
				<div class="wfb-remodal-header">
					<div class="wbfb_profile_pic">
						<img src="<?php echo esc_url( $account->avatar ); ?>" alt="" width="50" height="50" style="border-radius: 50px">
					</div>
					<div class="wbfb_profile_data">
						<div class="wbfb_profile_data_name">
							<a href="https://facebook.com/<?php echo esc_attr( $account->id ); ?>" target="_blank">
								<?php echo esc_html( $account->name ); ?>
							</a>
						</div>
						<div class="wbfb_post_data"><?php echo esc_html( time_elapsed_string( $post->created_time ) ); ?></div>
					</div>
				</div>
				<div class="wfb-remodal-text">
					<?php echo esc_html( $post->message ); ?>
				</div>
				<div class="wfb-remodal-stats">
					<div class="wbfb_masonry_post_footer">
						<div class="wbfb_masonry_post_share">
							<a href="https://facebook.com/<?php echo esc_attr( $post->id ); ?>" target="_blank">View on Facebook</a> |
							<a href="#">Share</a>
						</div>
						<div class="wbfb_masonry_post_stats">
							<ul class="wfb-meta wfb-light">
								<li class="wfb-likes">
									<span class="wfb-icon wfb-like">
										<svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Like" alt="Like"
										     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path ...></path>
										</svg>
									</span>
									<span class="wfb-count"><?php echo esc_html( $post->likes_count ); ?></span>
								</li>
								<li class="wfb-shares">
									<span class="wfb-icon wfb-share">
										<svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Share" alt="Share"
										     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                            <path ...></path>
										</svg>
									</span>
									<span class="wfb-count"><?php echo esc_html( $post->shares_count ); ?></span>
								</li>
								<li class="wfb-comments">
									<span class="wfb-icon wfb-comment">
										<svg width="24px" height="24px" role="img" aria-hidden="true" aria-label="Comment" alt="Comment"
										     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
											<path ...></path>
										</svg>
									</span>
									<span class="wfb-count"><?php echo esc_html( $post->comments_count ); ?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
<?php endforeach; ?>