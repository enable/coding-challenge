<h1>Videos Homepage</h1>

<table class="table table-bordered">
    <tr>
        <th>Title</th>
        <th>Speaker</th>
        <th>Date</th>
    </tr>
    <?php foreach($arrVideos as $arrVideo):?>
    <tr>
        <td><?= $arrVideo['Title'];?></td>
        <td><?= $arrVideo['Speaker'];?></td>
        <td><?= $arrVideo['PublicationDate'];?></td>
    </tr>
    <?php endforeach;?>
</table>