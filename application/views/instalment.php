<div class="row" style="margin-left: 0px; margin-right: 0px;">
    <?php foreach ($instalments as $instalment) { ?>
        <div class="text-center instalment_select" style="margin-bottom: 15px; cursor: pointer; background-color: #fff !important; padding: 5px; color: #666; border: solid 1px #e6e6e6;" data-count-number="<?php echo $instalment['count']; ?>">
            <div class="i-checks" style="margin-bottom: -10px;">
                <label>
                    <input class="check_<?php echo $instalment['count']; ?>" type="radio" value="<?php echo $instalment['count']; ?>" data-monthly="<?php echo $instalment['monthly']; ?>" data-total="<?php echo $instalment['total']; ?>" name="installment">
                </label>
            </div>
            <br>
            <span style="font-weight: 600;"><?php echo $instalment['count'] == 0 ? 'Tek Çekim' : $instalment['count'] . ' Taksit'; ?></span>
            <div class="hr-line-dashed" style="border: 1px solid #ddd; margin-top: 5px; margin-bottom: 5px;"></div>
            <span style="font-weight: 600;">Aylık Tutar</span>
            <br>
            <?php echo $instalment['monthly']; ?>
            <div class="hr-line-dashed" style="border: 1px solid #ddd; margin-top: 5px; margin-bottom: 5px;"></div>
            <span style="font-weight: 600;">Toplam Tutar</span>
            <br>
            <?php echo $instalment['total']; ?>
        </div>
    <?php } ?>
</div>