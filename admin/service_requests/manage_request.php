<?php 
require_once('./../../config.php');
if(isset($_GET['id'])){
    $stmt = $conn->prepare("SELECT s.*,c.category FROM `service_requests` s inner join `categories` c on s.category_id = c.id WHERE s.id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    foreach($result as $k => $v){
        $$k = $v;
    }
    $meta = $conn->prepare("SELECT * FROM `request_meta` WHERE request_id = ?");
    $meta->bind_param("i", $_GET['id']);
    $meta->execute();
    $res = $meta->get_result();
    while($row = $res->fetch_assoc()){
        ${$row['meta_field']} = $row['meta_value'];
    }
}
?>
<style>
    #uni_modal .modal-footer{ display:none }
    span.select2-selection.select2-selection--single,
    span.select2-selection.select2-selection--multiple {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0;
    }
</style>
<div class="container-fluid">
    <form action="" id="request_form">
        <input type="hidden" name="id" value="<?= isset($id) ? $id : "" ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Car Type</label>
                    <select name="category_id" class="form-select form-select-sm select2 rounded-0" required>
                        <option disabled selected></option>
                        <?php 
                        $category = $conn->query("SELECT * FROM `categories` WHERE status = 1 ORDER BY category ASC");
                        while($row = $category->fetch_assoc()):
                        ?>
                        <option value="<?= $row['id'] ?>" <?= isset($category_id) && $row['id'] == $category_id ? "selected" : "" ?>><?= $row['category'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Owner Fullname</label>
                    <input type="text" name="owner_name" class="form-control form-control-sm rounded-0" placeholder="e.g. John Doe" value="<?= $owner_name ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Owner Contact #</label>
                    <input type="text" name="contact" class="form-control form-control-sm rounded-0" placeholder="e.g. 08123456789" value="<?= $contact ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Owner Email</label>
                    <input type="email" name="email" class="form-control form-control-sm rounded-0" placeholder="e.g. example@mail.com" value="<?= $email ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea rows="3" name="address" class="form-control form-control-sm rounded-0" style="resize:none" required><?= $address ?? "" ?></textarea>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label>Car Name</label>
                    <input type="text" name="vehicle_name" class="form-control form-control-sm rounded-0" value="<?= $vehicle_name ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Car Registration Number</label>
                    <input type="text" name="vehicle_registration_number" class="form-control form-control-sm rounded-0" value="<?= $vehicle_registration_number ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Car Model</label>
                    <input type="text" name="vehicle_model" class="form-control form-control-sm rounded-0" value="<?= $vehicle_model ?? "" ?>" required>
                </div>
                <div class="form-group">
                    <label>Services</label>
                    <select name="service_id[]" class="form-select form-select-sm select2 rounded-0" multiple required>
                        <?php 
                        $service = $conn->query("SELECT * FROM `service_list` WHERE status = 1 ORDER BY `service` ASC");
                        while($row = $service->fetch_assoc()):
                        ?>
                        <option value="<?= $row['id'] ?>" <?= isset($service_id) && in_array($row['id'], explode(",", $service_id)) ? "selected" : "" ?>><?= $row['service'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Request Type</label>
                    <select name="service_type" id="service_type" class="form-select form-select-sm select2 rounded-0" required>
                        <option value="Drop Off" <?= isset($service_type) && $service_type == 'Drop Off' ? "selected" : '' ?>>Drop Off</option>
                        <option value="Pick Up" <?= isset($service_type) && $service_type == 'Pick Up' ? "selected" : '' ?>>Pick Up</option>
                    </select>
                </div>
                <div class="form-group" id="pickup-group" style="<?= isset($service_type) && $service_type == 'Drop Off' ? 'display:none' : '' ?>">
                    <label>Pick up Address</label>
                    <textarea rows="3" name="pickup_address" id="pickup_address" class="form-control form-control-sm rounded-0" style="resize:none"><?= $pickup_address ?? "" ?></textarea>
                </div>

                <hr class="border-light">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group" id="mechanic-holder">
                            <label>Assigned To:</label>
                            <select name="mechanic_id" id="mechanic_id" class="form-select form-select-sm rounded-0" required>
                                <option disabled <?= !isset($mechanic_id) ? "selected" : "" ?>></option>
                                <?php 
                                $mechanic = $conn->query("SELECT * FROM `mechanics_list` WHERE status = 1 ORDER BY `name` ASC");
                                while($row = $mechanic->fetch_assoc()):
                                ?>
                                <option value="<?= $row['id'] ?>" <?= isset($mechanic_id) && in_array($row['id'], explode(",", $mechanic_id)) ? "selected" : '' ?>><?= $row['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="custom-select custom-select-sm rounded-0" required>
                                <option value="0" <?= isset($status) && $status == 0 ? "selected" : '' ?>>Pending</option>
                                <option value="1" <?= isset($status) && $status == 1 ? "selected" : '' ?>>Confirmed</option>
                                <option value="2" <?= isset($status) && $status == 2 ? "selected" : '' ?>>On-Progress</option>
                                <option value="3" <?= isset($status) && $status == 3 ? "selected" : '' ?>>Done</option>
                                <option value="4" <?= isset($status) && $status == 4 ? "selected" : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 d-flex justify-content-end mx-2 mt-3">
            <div class="col-auto">
                <button class="btn btn-primary btn-sm rounded-0">Save Request</button>
                <button class="btn btn-light btn-sm rounded-0" type="button" data-dismiss="modal">Close</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(function(){
        $('.select2').select2({
            placeholder:"Please Select Here",
            dropdownParent: $('#uni_modal')
        });

        $('#mechanic_id').select2({
            placeholder:"Please Select Here",
            dropdownParent: $('#mechanic-holder')
        });

        $('#service_type').change(function(){
            if ($(this).val() === 'Pick Up') {
                $('#pickup-group').show();
                $('#pickup_address').attr('required', true);
            } else {
                $('#pickup-group').hide();
                $('#pickup_address').removeAttr('required');
            }
        }).trigger('change');

        $('#request_form').submit(function(e){
            e.preventDefault();
            start_loader();
            $.ajax({
                url: _base_url_ + 'classes/Master.php?f=save_request',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp){
                    end_loader();
                    if(resp.status == 'success'){
                        alert_toast("Data successfully saved", 'success');
                        setTimeout(() => {
                            uni_modal("Service Request Details", "service_requests/view_request.php?id=" + resp.id, 'large');
                            $('#uni_modal').on('hidden.bs.modal', function(){
                                location.reload();
                            });
                        }, 200);
                    } else {
                        alert_toast("An error occurred", 'error');
                    }
                }
            });
        });
    });
</script>
