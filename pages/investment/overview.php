<?php // $i->invetment_bot($investment_form);  ?>
<div class="d-flex justify-content-center">
    <div class="col-12 col-md-10 p-3">
        <h4 class="text-primary">Create A new Plan</h4>
        <div class="info card shadow-sm p-3 buttommovement">
            <div class='d-flex align-self-start'>
                <a href="#"><i class='ti ti-alert-circle p-2 fs-6'></i></a>
                <h5 class='ms-2'>Invest Your Funds</h5>
            </div>
            <p class='ms-5'>Create an investment plan and let our AI trading bot trade for you. Invest now, let your money start working for you.</p>
        </div>
        <div class="info card shadow-sm p-3 buttommovement">
            <div class='d-flex align-self-start'>
                <a href="#"><i class='ti ti-alert-circle p-2 fs-6'></i></a>
                <h5 class='ms-2'>High Return</h5>
            </div>
            <p class='ms-5'>Enjoy high daily returns on your investment.</p>
        </div>
        <div class="info card shadow-sm p-3 buttommovement">
            <div class='d-flex align-self-start'>
                <a href="#"><i class='ti ti-alert-circle p-2 fs-6'></i></a>
                <h5 class='ms-2'>Secured & Guranteed</h5>
            </div>
            <p class='ms-5'>Your funds are <b class='text-success'>100%</b> secured with us with a guranteed return on your investment.</p>
        </div>
        <p><?= $c->terms_message(); ?></p>
        <a href="?p=investment&action=new" class="btn btn-primary col-12 col-md-2">Create A Plan</a>
    </div>
</div>