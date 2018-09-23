<div class="wrap">
    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <form method="POST" class="invoice-bulk-download">
        <label for="download-filter"><?php esc_html_e( 'Download Filter', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
        <div class="input-group">
            <select name="filter" id="download-filter">
                <option value="month-group" selected><?php esc_html_e( 'Invoice Date', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                <option value="range-group"><?php esc_html_e( 'Custom Date Range', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
            </select>
        </div>
        <fieldset id="month-group">
            <div class="input-group">
                <div class="month-selector">
                    <label for="order-month"><?php esc_html_e( 'Month', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
                    <select name="order-month" id="order-month">
                        <option value="January"><?php esc_html_e( 'January', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="February"><?php esc_html_e( 'February', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="March"><?php esc_html_e( 'March', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="April"><?php esc_html_e( 'April', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="May"><?php esc_html_e( 'May', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="June"><?php esc_html_e( 'June', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="July"><?php esc_html_e( 'July', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="August"><?php esc_html_e( 'August', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="September"><?php esc_html_e( 'September', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="October"><?php esc_html_e( 'October', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="November"><?php esc_html_e( 'November', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                        <option value="December"><?php esc_html_e( 'December', 'woocommerce-pdf-invoices-bulk-download' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <div class="year-selector">
                    <label for="order-year"><?php esc_html_e( 'Year', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
                    <select name="order-year" id="order-year">
                    <?php
                        $now   = date( 'Y' );
                        $then  = $now - 10;
                        $years = range( $then, $now );
                        foreach( $years as $year ) :
                        ?>
                            <option value="<?php echo esc_attr( $year ); ?>" <?php if ( $now == $year ): ?>selected<?php endif; ?>><?php echo esc_html( $year ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>
        <fieldset id="range-group" disabled>
            <div class="input-group">
                <div class="date-selector">
                    <label for="start-date"><?php esc_html_e( 'Start Date', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
                    <input class="datepicker" required name="start-date" id="start-date" value="">
                </div>
            </div>
            <div class="input-group">
                <div class="date-selector">
                    <label for="end-date"><?php esc_html_e( 'End Date', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
                    <input class="datepicker" required name="end-date" id="end-date" value="">
                </div>
            </div>
        </fieldset>
        <fieldset id="checkbox-group">
            <label for="status-filter"><?php esc_html_e( 'Order Status', 'woocommerce-pdf-invoices-bulk-download' ); ?></label>
            <div class="input-group">
                <?php foreach( $this->get_all_order_statuses() as $order_key => $order_status ) {
                    ob_start();
                    ?>
                    <div class="status-option">
                        <input type="checkbox" id="<?php echo esc_attr( $order_key ); ?>" name="order-statuses[]" value="<?php echo esc_attr( $order_key ); ?>" checked>
                        <label for="<?php echo esc_attr( $order_key ); ?>"><?php echo esc_html( $order_status ); ?></label>
                    </div>
                <?php ob_end_flush();
                } ?>
            </div>
        </fieldset>
        <div class="control-group">
            <?php submit_button( esc_html__( 'Download Invoices', 'woocommerce-pdf-invoices-bulk-download' ), 'primary', 'submit', false ); ?>
            <div class="spinner"></div>
        </div>
        <div class="messages__wrap">
            <div class="message__item message__item--processing"><?php esc_html_e( 'Archive is preparing. Please, wait...', 'woocommerce-pdf-invoices-bulk-download' ); ?></div>
            <div class="message__item message__item--success"><?php esc_html_e( 'Archive was created successfully.', 'woocommerce-pdf-invoices-bulk-download' ); ?></div>
            <div class="message__item message__item--error"></div>
        </div>
    </form>
</div>
