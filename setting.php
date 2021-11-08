<form method="POST">
    <h1>Pengaturan OpenSID Konektor</h1>
    <table class="form-table">
        <thead><tr><th colspan="2">Koneksi Data OpenSID</th></tr></thead>
        <tbody>
            <tr>
                <th>Alamat Web OpenSID</th>
                <td>
                    <input type="text" name="opensid[url]" value="<?php echo esc_attr(@self::$config['opensid']['url']) ?>"/>
                </td>
            </tr>
            <tr>
                <th>User Pict Path</th>
                <td>
                    <input type="text" name="opensid[foto_path]" value="<?php echo esc_attr(@self::$config['opensid']['foto_path']) ?>"/>
                </td>
            </tr>
            <tr>
                <th>Foto Default</th>
                <td>
                    <input type="text" name="opensid[foto_default]" value="<?php echo esc_attr(@self::$config['opensid']['foto_default']) ?>"/>
                </td>
            </tr>
            <tr>
                <th>Jenis Koneksi</th>
                <td>
                    <select id="opensid-konektor-type" name="konektor[type]">
                        <option value="db">Database</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <div id="konektor-setting">
    </div>
    <p class="submit">
        <input type="submit" name="submit" class="button button-primary" value="Simpan">
    </p>
    <input type="hidden" name="nonce" id="opensid-konektor-nonce" value="<?php echo wp_create_nonce() ?>"/>
</form>
<script type="text/javascript">

    jQuery(function ($) {
        function load_konektor_setting() {
            var selected = $('#opensid-konektor-type').val();
            $.post("<?php echo admin_url( 'admin-ajax.php' ) ?>", {
                action: 'opensid_konektor_setting',
                nonce: $('#opensid-konektor-nonce').val(),
                type: selected
            }, function (data) {
                $('#konektor-setting').html(data);
            });
        }
        $('#opensid-konektor-type').change(load_konektor_setting);
        load_konektor_setting();
    })
</script>
<?php
    $items = apply_filters( "opensid_extensions", [] );
    $total = count($items);
?>
<table class="form-table extension-setting">
    <thead><tr><th colspan="3">Pembaruan &amp; Ekstensi (<?= $total ?>)</th></tr></thead>
    <tbody>
        <?php foreach ($items as $key => $item): 
        if( @$item['update_git'] ){
            $link = esc_url( add_query_arg(
                [
                    'page' => $_GET['page'],
                    'update_git' => $item['name']
                ],
                get_admin_url() . 'options-general.php?'
            ) );
        }else{
            $link = plugin_dir_url( $item['file'] ) . $item['update_link'];
        }
        ?>
        <tr>
            <td><?php echo esc_html( $item['title']) ?></td>
            <td><code><?php echo esc_html( $item['version']) ?></code></td>
            <td>
                <a href="<?php echo esc_attr( $link ) ?>">Check &amp; Update</a>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>