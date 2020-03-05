<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 b-r">
            <p class="menu-title p-t-10">GRUPY</p>
            <ul class="no-padding list-unstyled groups-list">
                <li class="active">
                    <a href="<?php echo base_url(); ?>">
                        Wszystkie
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Baterie 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        DNA 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Kabel USB 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Karta SIM 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Kaseta 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Komputery/ Tablety 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Ładowarki 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Materiały do alarmów 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Materiały do Navi Box 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Materiały do Navi Cash 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Materiały do pakietów 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Materiały do walizek 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Pojemniki 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Prudukt niezgodny 
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url(); ?>">
                        Zamki 
                    </a>
                </li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="card card-transparent">
                <div class="card-block">
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group p-0">
                            <a href="<?php echo base_url('warehouse/receive'); ?>" class="btn btn-default disabled w-100">
                                <span class="p-t-5 p-b-5">
                                    <i class="fa fa-mail-reply"></i>
                                </span>
                                <br>
                                <span class="fs-11 font-montserrat text-uppercase">Przyjmij</span>
                            </a>
                        </div>
                        <div class="btn-group p-0">
                            <a href="<?php echo base_url('warehouse/issuing'); ?>" class="btn btn-default disabled w-100">
                                <span class="p-t-5 p-b-5">
                                    <i class="fa fa-share"></i>
                                </span>
                                <br>
                                <span class="fs-11 font-montserrat text-uppercase">Wydaj</span>
                            </a>
                        </div>
                        <div class="btn-group p-0">
                            <a href="<?php echo base_url('warehouse/issuing'); ?>" class="btn btn-default disabled text-red w-100">
                                <span class="p-t-5 p-b-5">
                                    <i class="fa fa-trash-o"></i>
                                </span>
                                <br>
                                <span class="fs-11 font-montserrat text-uppercase">usuń</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-default">
                <div class="card-header">
                    <div class="input-group input-group-borderless">
                        <div class="input-group-btn">
                            <button class="btn btn-link"><i class="fa fa-search"></i></button>
                        </div>
                        <input type="text" class="form-control fs-12" placeholder="Szukaj...">
                    </div>
                </div>
                <div class="card-block b-thick">
                    <table class="table table-hover table-responsive-block dataTable no-footer">
                        <thead>
                            <tr>
                                <th>Nazwa</th>
                                <th>Kod</th>
                                <th>Ilość</th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < 15; $i++): ?>
                                <tr class="tr-link" data-href="<?php echo base_url('warehouse/product/' . $i); ?>">
                                    <td class="clickable">
                                        Produkt <?php echo $i; ?>
                                    </td>
                                    <td class="clickable">
                                        PR<?php echo $i; ?>
                                    </td>
                                    <td class="clickable">
                                        <?php echo rand(0, rand(20, 30)); ?>
                                    </td>
                                    <td>
                                        <span class="m-r-5 text-primary" tooltip="Podgląd">
                                            <i class="fs-16 fa fa-eye"></i>
                                        </span>
                                        <span class="m-l-5 text-danger">
                                            <i class="fs-16 fa fa-trash-o"></i>
                                        </span>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
