<div class="body">
  <h1>$Title</h1>
  $Content
</div>
<div class="ultracart">
  <% loop Products %>
  <div class="ultracart--product" data-UCItemID="$UltracartItemID">
    <h3>$Title</h3>
    <p>$Content.FirstParagraph.XML</p>
    <p><a href="$Link">View Item</a></p>
  </div>
  <% end_loop %>
</div>
