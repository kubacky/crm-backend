<div class="container-fluid">
    <div class="row no-padding">
        <div class="col-md-3 p-l-0">
            <ul class="no-padding list-unstyled docs-list b-r">
                <?php
                $year = date('Y');
                $types = array('WZ', 'PZ', 'PW', 'RW');
                $names = array('Wydanie zewnętrzne', 'Przyjęcie zewnętrzne', 'Przyjęcie wewnętrzne', 'Rozchód wewnętrzny');

                for ($i = 1; $i < 20; $i++):
                    $day = rand(1, 28);
                    $month = rand(1, 12);
                    $doc = rand(0, 3);
                    $date = formatDate($year . '-' . $month . '-' . $day, 'j n Y');
                    $number = $types[$doc] . ' ' . $i . rand(20, 40) . '/' . $year;
                    ?>
                    <li class="item p-t-10 p-b-10 pointer <?php if ($i == 1) echo 'active' ?>">
                        <div class="inline">
                            <p class="recipients no-margin hint-text small"><?php echo $date; ?></p>
                            <p class="subject no-margin bold"><?php echo $number; ?></p>
                            <p class="no-margin"><small><?php echo $names[$doc]; ?></small></p>
                        </div>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-transparent">
                        <div class="card-block">
                            <div class="btn-group btn-group-justified">
                                <div class="btn-group p-0">
                                    <a href="<?php echo base_url('warehouse/print'); ?>" class="btn btn-default w-100">
                                        <span class="p-t-5 p-b-5">
                                            <i class="fa fa-print"></i>
                                        </span>
                                        <br>
                                        <span class="fs-11 font-montserrat text-uppercase">Drukuj</span>
                                    </a>
                                </div>
                                <div class="btn-group p-0">
                                    <a href="<?php echo base_url('warehouse/edit/document'); ?>" class="btn btn-default w-100">
                                        <span class="p-t-5 p-b-5">
                                            <i class="fa fa-edit"></i>
                                        </span>
                                        <br>
                                        <span class="fs-11 font-montserrat text-uppercase">Edytuj</span>
                                    </a>
                                </div>
                                <div class="btn-group p-0">
                                    <a href="<?php echo base_url('warehouse/delete/document'); ?>" class="btn btn-default w-100">
                                        <span class="p-t-5 p-b-5">
                                            <i class="fa fa-trash-o"></i>
                                        </span>
                                        <br>
                                        <span class="fs-11 font-montserrat text-uppercase">Usuń</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card card-default padding-25 p-b-50 p-t-50">
                        <div class="card-header separator text-center">
                            <h6 class="bold">Wydanie zewnętrzne nr WZ 1202/2017</h6>
                        </div>
                        <div class="card-block">
                            <div class="fluid-container m-t-25">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <address>
                                            <strong>Centralne Biuro Zabezpieczeń</strong><br>
                                            ul. Łódzka 1<br>
                                            14-100 Ostróda
                                            <p>NIP 739-284-74-03 </p>
                                        </address>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                            <div class="col-sm-6 text-right">
                                                Data utworzenia: 
                                            </div>
                                            <div class="col-sm-6">
                                                <strong>20 września 2017</strong>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                Sporządził:
                                            </div>
                                            <div class="col-sm-6">
                                                <strong>Żaneta Ceran</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row m-t-40">
                                    <div class="col-sm-12">
                                        <p>Lista towarów</p>
                                    </div>
                                    <div class="col-sm-12">
                                        <table class="table table-striped table-condensed">
                                            <thead>
                                                <tr>
                                                    <td style="width: 50px;">Lp.</td>
                                                    <td>Nazwa towaru</td>
                                                    <td class="text-center" style="width: 100px;">Ilość</td>
                                                    <td class="text-right" style="width: 50px;">J.m.</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>1</td>
                                                    <td>Produkt 1</td>
                                                    <td class="text-center">12</td>
                                                    <td class="text-right" >szt.</td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>Produkt 2</td>
                                                    <td class="text-center">100</td>
                                                    <td class="text-right" >m.</td>
                                                </tr>
                                                <tr>
                                                    <td>3</td>
                                                    <td>Produkt 3</td>
                                                    <td class="text-center">1</td>
                                                    <td class="text-right" >szt.</td>
                                                </tr>
                                                <tr>
                                                    <td>4</td>
                                                    <td>Produkt 4</td>
                                                    <td class="text-center">9</td>
                                                    <td class="text-right" >szt.</td>
                                                </tr>
                                                <tr>
                                                    <td>5</td>
                                                    <td>Produkt 5</td>
                                                    <td class="text-center">1</td>
                                                    <td class="text-right" >szt.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row m-t-40 p-b-50">
                                    <div class="col-sm-6 text-cener">
                                        <p class="no-margin">Wydający towar:</p>
                                        <p class="no-margin bold">Żaneta Ceran</p>
                                    </div>
                                    <div class="col-sm-6 text-cener">
                                        <p class="no-margin">Przyjmujący towar:</p>
                                        <p class="no-margin bold">S.P.P. Zakład Opieki Zdrowotnej Choroszcz</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
