class ProgressMessageBox{

    static get STATUS_LOADING(){
        return 0;
    }
    static get STATUS_SUCCESS(){
        return 1;
    }
    static get STATUS_FAIL(){
        return 2;
    }

    id;
    title;
    header;
    message;
    destroyOnHide;
    onSuccess;
    onFail;
    status = ProgressMessageBox.STATUS_LOADING;
    modal;
    isCreated = false;

    constructor(id, title, header, message, destroyOnHide = false){
        this.id = id;
        this.title = title;
        this.header = header;
        this.message = message;
        this.destroyOnHide = destroyOnHide;

        this.init();
    }

    init() {
        if(!this.isCreated){
            this.modal = document.createElement("div");
            this.modal.id = this.id;
            this.modal.classList.add("modal", "progress-message-box");
            this.modal.setAttribute("tabindex", "-1");

            var html = '<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">';
            html += this.title;
            html += '</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button></div><div class="modal-body">'
            html += '<strong>' + this.header + '</strong><div class="d-flex justify-content-center" style="margin: 1em 0;"><div class="spinner-border" role="status" aria-hidden="true"></div><div class="spinner-ready"><svg class="bi"><use xlink:href="img/ui-icons.svg#check-circle"/></svg></div><div class="spinner-failed"><svg class="bi"><use xlink:href="img/ui-icons.svg#x-circle"/></svg></div></div>';
            html += '<p class="progress-message">' + this.message + '</p>';
            html += '</div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button></div></div></div></div>';

            this.modal.innerHTML = html;

            document.body.appendChild(this.modal);

            var that = this;
            if(this.destroyOnHide){
                $("#" + this.id).on('hidden.bs.modal', function (e) {
                    that.destroy();
                  })
            }

            this.isCreated = true;
        }
    }

    show() {
        $("#" + this.id).modal("show");
    }

    hide() {
        $("#" + this.id).modal("hide");
    }

    destroy() {
        if(this.isCreated){
            document.body.removeChild(this.modal);
            this.isCreated = false;
        }
    }

    setMessage(message){
        this.message = message;
        if(this.isCreated){
            $("#" + this.id + " .progress-message").text(message);
        }
    }

    setStatus(status){
        if(ProgressMessageBox.STATUS_LOADING <= status <= ProgressMessageBox.STATUS_FAIL){
            this.status = status;
            switch(status){
                case ProgressMessageBox.STATUS_LOADING:
                    this.modal.classList.remove("ready", "failed");
                    break;
                case ProgressMessageBox.STATUS_SUCCESS:
                    this.modal.classList.add("ready");
                    if(this.onSuccess != undefined){
                        this.onSuccess();
                    }
                    break;
                case ProgressMessageBox.STATUS_FAIL:
                    this.modal.classList.add("failed");
                    if(this.onFail != undefined){
                        this.onFail();
                    }
                    break;
            }
        }
    }

    setEventListener(event, listener){
        switch(event){
            case "onSuccess":
                this.onSuccess = listener;
                break;
            case "onFail":
                this.onFail = listener;
                break;
        }
    }
}