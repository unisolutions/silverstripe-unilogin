<div class="unilogin-container">
	<h2>$Title</h2>
	<p>$Content</p>
	<table class="table table-striped table-hover">
		<thead>
			<tr>
				<th><%t UniLogIn.ColID 'ID' %></th>
				<th><%t UniLogIn.ColTitle 'Title' %></th>
				<th><%t UniLogIn.ColEmail 'Email' %></th>
				<th><%t UniLogIn.ColGroups 'Groups' %></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<% loop Members %>
				<tr>
					<td>$ID</td>
					<td>$Title</td>
					<td><a href="mailto:$Email">$Email</a></td>
					<td><% loop $DirectGroups %><% if not $First %>, <% end_if %>$Title<% end_loop %></td>
					<td>
						<a class="btn btn-primary btn-xs btn-block" href="/$Top.URLSegment/as_member/$ID">
							<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span>
							<%t UniLogIn.LogIn 'login' %>
						</a>
					</td>
				</tr>
			<% end_loop %>
		</tbody>
	</table>
</div>