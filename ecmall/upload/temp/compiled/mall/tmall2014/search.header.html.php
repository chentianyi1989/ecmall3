<?php echo $this->fetch('top.html'); ?>
<div id="header" class="w-full">
    <div class="shop-t w clearfix pb10 mb5 mt5">
        <div class="logo">
            <a href="<?php echo $this->_var['site_url']; ?>" title="<?php echo $this->_var['site_title']; ?>"><img alt="<?php echo $this->_var['site_title']; ?>" src="<?php echo $this->_var['site_logo']; ?>" /></a>
        </div>
        <div class="top-search">
            <div class="top-search-box clearfix">
                <ul class="top-search-tab clearfix">
                    <li id="index" class="current"><span>商品</span></li>
                    <li id="store"><span>店铺</span></li>
                    <li id="groupbuy"><span>团购</span></li>
                </ul>
                <div class="form-fields">
                    <form method="GET" action="<?php echo url('app=search'); ?>">
                        <input type="hidden" name="app" value="search" />
                        <input type="hidden" name="act" value="<?php if ($_GET['act'] == 'store'): ?>store<?php elseif ($_GET['act'] == 'groupbuy'): ?>groupbuy<?php else: ?>index<?php endif; ?>" />
                        <input type="text"   name="keyword" value="<?php echo $_GET['keyword']; ?>" class="keyword <?php if (! $_GET['keyword']): ?>kw_bj <?php if ($_GET['act'] == 'store'): ?>store<?php elseif ($_GET['act'] == 'groupbuy'): ?>groupbuy<?php else: ?>index<?php endif; ?>_bj <?php endif; ?>" />
                        <input type="hidden" name="cate_id" value="<?php echo htmlspecialchars($_GET['cate_id']); ?>" />
                        <input type="submit" value="搜索" class="submit" hidefocus="true" />
                    </form>
                </div>
            </div>
            <div class="top-search-keywords">
                <?php $_from = $this->_var['hot_keywords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'keyword');$this->_foreach['fe_keyword'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fe_keyword']['total'] > 0):
    foreach ($_from AS $this->_var['keyword']):
        $this->_foreach['fe_keyword']['iteration']++;
?>
                <a <?php if ($this->_foreach['fe_keyword']['iteration'] % 3 == 1): ?>style="color:#c40000;"<?php endif; ?> href="<?php echo url('app=search&keyword=' . urlencode($this->_var['keyword']). ''); ?>"><?php echo $this->_var['keyword']; ?></a>|
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            </div>
        </div>
        <div class="header_cart">
            <div class="title clearfix">
                <b></b><a href="<?php echo url('app=cart'); ?>">去购物车结算</a><em></em>
            </div>
            <div class="shoping"><span class="count-cart J_C_T_GoodsKinds"><?php echo $this->_var['cart_goods_kinds']; ?></span></div>
        </div>
    </div>
</div>