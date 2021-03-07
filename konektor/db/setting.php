<?php
$setting = self::$config['konektor']['setting'];

?><table class="form-table setting">
    <tbody>
        <tr>
            <th>Host/IP</th>
            <td>
                <input name="konektor[setting][host]" type="text" value="<?php echo esc_attr( @$setting['host'] ) ?>" />
            </td>
        </tr>
        <tr>
            <th>Username</th>
            <td>
                <input name="konektor[setting][username]" type="text" value="<?php echo esc_attr( @$setting['username'] ) ?>" />
            </td>
        </tr>
        <tr>
            <th>Password</th>
            <td>
                <input name="konektor[setting][password]" type="password" value="<?php echo esc_attr( @$setting['password'] ) ?>" />
            </td>
        </tr>
        <tr>
            <th>Database</th>
            <td>
                <input name="konektor[setting][database]" type="text" value="<?php echo esc_attr( @$setting['database'] ) ?>" />
            </td>
        </tr>
    </tbody>
</table>