<?php

use com\cminds\videolesson\model\Micropayments;

echo $addForm;

?>

<?php if ($pageUrl != preg_replace('/\&p=[0-9]+/', '', $currentUrl)): ?>
	<p><a href="<?php echo esc_attr($pageUrl); ?>">&laquo; Back to full report</a></p>
<?php endif; ?>

<form method="GET" class="cmvl-report-filter">
	<input type="hidden" name="page" value="<?php echo $pageMenuSlug; ?>" />
	<table class="wp-list-table widefat fixed cmvl-report-table">
		<thead>
			<tr>
				<th>Channel</th>
				<th>User</th>
				<th>Start</th>
				<th>End</th>
				<th>Duration</th>
				<th>Points</th>
				<th>Status
					<select name="status">
						<option value="">any</option>
						<option value="active"<?php selected($filter['status'], 'active'); ?>>active</option>
						<option value="past"<?php selected($filter['status'], 'past'); ?>>past</option>
					</select>
				</th>
			</tr>
		</thead>
		<tbody><?php if (!empty($data)) foreach ($data as $row): ?>
			<tr>
				<td>
					<a href="<?php echo esc_attr(admin_url('post.php?action=edit&post=' . $row['post_id'])); ?>"><?php echo $row['post_title']; ?></a>
					<a href="<?php echo esc_attr(add_query_arg('post_id', $row['post_id'], $pageUrl)); ?>" class="cmvl-report-row-filter">Filter</a>
				</td>
				<td>
					<a href="<?php echo esc_attr(admin_url('profile.php?user_id=' . $row['user_id'])); ?>"><?php echo $row['user_name']; ?></a>
					<a href="<?php echo esc_attr(add_query_arg('user_id', $row['user_id'], $pageUrl)); ?>" class="cmvl-report-row-filter">Filter</a>
				</td>
				<td><?php echo Date('Y-m-d H:i:s', $row['start']); ?></td>
				<td><?php echo Date('Y-m-d H:i:s', $row['end']); ?></td>
				<td><?php echo Micropayments::seconds2period($row['duration']); ?></td>
				<td><?php echo $row['points']; ?></td>
				<td><?php echo (($row['end'] < time()) ? 'past' : 'active'); ?></td>
			</tr>
		<?php endforeach; ?></tbody>
	</table>
</form>

<?php if ($pagination['lastPage'] > 1): ?>
	<ul class="cmvl-pagination"><?php for ($page=1; $page<=$pagination['lastPage']; $page++): ?>
		<li<?php if ($page == $pagination['page']) echo ' class="current-page"';
			?>><a href="<?php echo esc_attr(add_query_arg('p', $page, $pagination['firstPageUrl'])); ?>"><?php echo $page; ?></a></li>
	<?php endfor; ?></ul>
<?php endif; ?>

<?php if (empty($data)): ?>
	<p>No data.</p>
<?php endif; ?>