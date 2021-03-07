<form method="POST">
    <h1>Pengaturan OpenSID Konektor</h1>
    <table class="form-table">
        <tbody>
            <tr>
                <th>Alamat Web OpenSID</th>
                <td>
                    <input type="text" name="opensid[url]" value="<?php echo esc_attr(@self::$config['opensid']['url']) ?>"/>
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
            console.log("LOAD KONEKTOR SETTINGS");

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