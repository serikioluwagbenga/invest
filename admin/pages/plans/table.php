<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="border-bottom title-part-padding">
        <h4 class="card-title mb-0">All Plans</h4>
        <a href="plans?p=plans&action=new" class='btn btn-primary'> +  Add new plan</a>
      </div>
      <div class="card-body">
        <table class="table border text-nowrap customize-table mb-0 align-middle">
          <thead>
            <tr>
              <th>Min - Max Amount</th>
              <th>Return</th>
              <th>Top Investors</th>
              <th>Status</th>
            </tr>
            <?php foreach ($plans as $row) { ?>
              <tr>
                <td>
                  <b><?= number_format($row['min_amount']) . ' - ' . number_format($row['max_amount']) . " " . $row['plan_name'] ?></b>
                </td>
                <td>
                  <p class="mb-0 fw-normal fs-4"><?= "Return: " . $row['return_range_from'] . "% - " . $row['return_range_to'] . "% (" . $row['retrun_interval'] . ")" ?></p>
                </td>

                <td>
                  <div class="d-flex align-items-center">
                    <a href="#" title="User Name here" class="bg-secondary text-white fs-6 rounded-circle me-n2 card-hover border border-2 border-white d-flex align-items-center justify-content-center" style="width: 39px; height: 39px;">
                      S
                    </a>
                    <a href="#" class="bg-danger text-white fs-6 rounded-circle me-n2 card-hover border border-2 border-white d-flex align-items-center justify-content-center" style="width: 39px; height: 39px;">
                      D
                    </a>
                  </div>
                </td>
                <td>
                  <?php echo $c->badge($row['status']); ?>

                </td>
                <td>
                  <div class="dropdown dropstart">
                    <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="ti ti-dots-vertical fs-6"></i>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-3" href="plans?action=edit&id=<?= $row['ID'] ?>"><i class="fs-4 ti ti-edit"></i>Edit</a>
                      </li>
                      <li>
                        <form action="" id="foo">
                          <input type="hidden" name="deleteplan" value="<?= $row['ID'] ?>">
                          <input type="hidden" name="confirm" value="Deleting this plan will close all trades on the plan add both capital and profit to balance">
                          <input type="hidden" name="page" value="plans">
                          <div id="custommessage"></div>
                          <button type="submit" class="dropdown-item d-flex align-items-center gap-3 text-danger" href="#"><i class="fs-4 ti ti-trash"></i>Remove</button>
                        </form>
                      </li>
                    </ul>
                  </div>
                </td>



              </tr>
            <?php } ?>
          </thead>
          <tbody>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>