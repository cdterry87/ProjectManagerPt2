<table class="table is-striped is-narrow is-hoverable is-fullwidth">
    <thead>
        <tr>
            <th>Download</th> 
            <th>Title</th> 
            <th>Description</th> 
            <th>File</th> 
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
        <?php
            if (empty($documentation)) {
        ?>
        <tr>
            <td colspan="2">No documentation at this time.</td>
        </tr>
        <?php
            } else {
                foreach ($documentation as $row) {
        ?>
        <tr>
            <td><?php echo anchor('public/files/documentation/'.$row['file_id']."/".$row['file_name'], '<i class="fas fa-download"></i>Download', 'target="_blank"'); ?></td>
            <td><?php echo $row['file_title']; ?></td>
            <td><?php echo $row['file_description']; ?></td>
            <td><?php echo $row['file_name']; ?> (<?php echo $row['file_size']; ?>)</td>
            <td><?php echo anchor('files/delete_documentation/'.$row['file_id'], '<i class="fas fa-trash"></i>', 'class="button is-danger is-fullwidth"'); ?></td>
        </tr>
        <?php
                }
            }
        ?>
    </tbody>
</table>
