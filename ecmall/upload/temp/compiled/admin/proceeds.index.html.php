<?php echo $this->fetch('header.html'); ?>
<script type="text/javascript">
$(function(){
    $('#pay_time_from').datepicker({dateFormat: 'yy-mm-dd'});
    $('#pay_time_to').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>
<div id="rightTop">
    <p>平台统一收款单</p>
    <ul class="subnav">
        <li><span>管理</span></li>
        <!-- <li><a class="btn1" href="index.php?app=proceeds&amp;act=export">导出</a></li> -->
    </ul>
</div>
<div class="mrightTop">
    <div class="fontl">
        <form method="get">
             <div class="left">
                <input type="hidden" name="app" value="proceeds" />
                <input type="hidden" name="act" value="index" />
                <select class="querySelect" name="field"><?php echo $this->html_options(array('options'=>$this->_var['search_options'],'selected'=>$_GET['field'])); ?>
                </select>:<input class="queryInput" type="text" name="search_name" value="<?php echo htmlspecialchars($this->_var['query']['search_name']); ?>" />
                下单时间从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['pay_time_from']; ?>" id="pay_time_from" name="pay_time_from" class="pick_date" />
                至:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['pay_time_to']; ?>" id="pay_time_to" name="pay_time_to" class="pick_date" />
                支付金额从:<input class="queryInput2" type="text" value="<?php echo $this->_var['query']['money_from']; ?>" name="money_from" />
                至:<input class="queryInput2" type="text" style="width:60px;" value="<?php echo $this->_var['query']['money_to']; ?>" name="money_to" class="pick_date" />
                <input type="submit" class="formbtn" value="查询" />
            </div>
            <?php if ($this->_var['filtered']): ?>
            <a class="left formbtn1" href="index.php?app=proceeds">撤销检索</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="fontr">
        <?php if ($this->_var['proceeds']): ?><?php echo $this->fetch('page.top.html'); ?><?php endif; ?>
    </div>
</div>
<div class="tdare">
    <table width="100%" cellspacing="0" class="dataTable">
        <?php if ($this->_var['proceeds']): ?>
        <tr class="tatr1">
            <td width="5%" class="firstCell"><input type="checkbox" class="checkall" /></td>
            <td width="15%"><span ectype="order_by" fieldname="seller_id">店铺名称</span></td>
            <td width="10%"><span ectype="order_by" fieldname="order_sn">订单号</span></td>
            <td width="15%"><span ectype="order_by" fieldname="pay_time">支付时间</span></td>
            <td width="10%"><span ectype="order_by" fieldname="buyer_name">买家名称</span></td>
            <td width="10%"><span ectype="order_by" fieldname="money">支付金额</span></td>
            <td>支付方式</td>
            <td width="10%"><span ectype="order_by" fieldname="pay_status">支付状态</span></td>
            <!-- <td width="10%"><span ectype="order_by" fieldname="balance">卖家当前余额</span></td> -->
            <td>支付类型</td>
            <!-- <td width="15%">支付备注</td> -->
        </tr>
        <?php endif; ?>
        <?php $_from = $this->_var['proceeds']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'proceed');if (count($_from)):
    foreach ($_from AS $this->_var['proceed']):
?>
        <tr class="tatr2">
            <td class="firstCell"><input type="checkbox" class="checkitem" value="<?php echo $this->_var['proceed']['log_id']; ?>"/></td>
            <td><?php echo htmlspecialchars($this->_var['proceed']['seller_name']); ?></td>
            <td><?php echo $this->_var['proceed']['order_sn']; ?></td>
            <td><?php echo local_date("Y-m-d H:i:s",$this->_var['proceed']['pay_time']); ?></td>
            <td><?php echo htmlspecialchars($this->_var['proceed']['buyer_name']); ?></td>
            <td><?php echo price_format($this->_var['proceed']['money']); ?></td>
            <td><?php echo (htmlspecialchars($this->_var['proceed']['payment_name']) == '') ? '-' : htmlspecialchars($this->_var['proceed']['payment_name']); ?></td>
            <td><?php echo call_user_func("proceeds_status",$this->_var['proceed']['pay_status']); ?></td>
            <!-- <td><?php echo price_format($this->_var['proceed']['balance']); ?></td> -->
            <td><?php echo call_user_func("pay_type",$this->_var['proceed']['type']); ?></td>
            <!-- <td><?php echo (htmlspecialchars($this->_var['proceed']['pay_message']) == '') ? '-' : htmlspecialchars($this->_var['proceed']['pay_message']); ?></td> -->
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
            <td colspan="7">没有符合条件的记录</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </table>
    <div id="dataFuncs">
        <div id="batchAction" class="left paddingT15">
        <input class="formbtn batchButton" type="button" value="导出" name="log_id" uri="index.php?app=proceeds&act=export&ret_page=<?php echo $this->_var['page_info']['curr_page']; ?>"/>
        </div>
        <div class="pageLinks">
            <?php if ($this->_var['proceeds']): ?><?php echo $this->fetch('page.bottom.html'); ?><?php endif; ?>
        </div>
    </div>
    <div class="clear"></div>
</div>
<?php echo $this->fetch('footer.html'); ?>
