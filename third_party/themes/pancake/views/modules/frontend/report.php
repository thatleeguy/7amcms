<table class="report-contents timesheet-table">
    <thead>
        <tr>
            <?php foreach ($fields as $field => $title) : ?>
                <th class="<?php echo $field; ?>"><?php echo $title; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>   
    <tbody>
        <?php foreach ($records as $record) : ?>
            <tr>
                <?php foreach (array_keys($fields) as $field) : ?>
                    <td><?php echo $record[$field]; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>    
    </tbody>
    <tfoot>
        <tr>
            <?php foreach (array_keys($fields) as $field) : ?>
                <?php if (isset($field_totals[$field])) : ?>
                    <th class="<?php echo $field; ?>"><?php echo $field_totals[$field]; ?></th>
                <?php else: ?>
                    <th></th>
                <?php endif;?>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>