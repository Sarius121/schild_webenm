class Requests {

    request;
    requestQueue;
    isRequesting = false;
    csrfToken;
    debug = false;
    curOnload = null;

    constructor(csrfToken){
        this.csrfToken = csrfToken;
        this.requestQueue = [];
        this.request = new XMLHttpRequest();
        var that = this;
        this.request.onreadystatechange = function(){
            if (this.readyState == 4) {
                if(this.status == 200){
                    if(that.debug){
                        console.log(this.responseText);
                    }
                    try{
                        var result = JSON.parse(this.responseText);
                    } catch(e){
                        console.log("data couldn't be parsed");
                        that.startRequest();
                        return;
                    }
                    if(result.code == 0){
                        // success
                        if(that.curOnload != null){
                            try {
                                that.curOnload(result);
                            } catch (error) {
                                
                            }
                            that.curOnload = null;
                        }
                        that.csrfToken = result.csrf_token;
                        that.clearError();
                    } else {
                        // error
                        if(that.debug){
                            console.log(result.message);
                            console.log(result.details);
                        }
                        if(result.code == 21){
                            // wrong csrf token -> reload to get current token
                            location.reload();
                        }
                        if("csrf_token" in result){
                            that.csrfToken = result.csrf_token;
                        }
                        that.displayError(result.code, result.message, result.errorid);
                        if(that.curOnload != null){
                            try {
                                that.curOnload(result);
                            } catch (error) {
                                
                            }
                            that.curOnload = null;
                        }
                    }
                } else if(this.status == 403){
                    // new login required
                    location.reload();
                } else {
                    //TODO that.displayError("Verbindung zum Server unterbrochen!");
                }
                that.startRequest();
            }
        };
    }

    /**
     * 
     * @param {string} method 
     * @param {string} target 
     * @param {FormData} data 
     * @param {function} onload 
     */
    addRequestToQueue(method, target, data, onload){
        this.requestQueue.push({method: method, target: target, data: data, onload: onload});
        if(!this.isRequesting){
            this.startRequest();
        }
    }

    startRequest(){
        if(this.requestQueue.length == 0){
            this.isRequesting = false;
            return;
        }
        this.isRequesting = true;

        var curItem = this.requestQueue.shift();
        curItem.data.append("csrf_token", this.csrfToken);
        this.curOnload = curItem.onload;
        this.request.open(curItem.method, curItem.target);
        this.request.send(curItem.data);
    }

    /**
     * display error in alert box
     * 
     * @param {string} code error code
     * @param {string} message error message
     * @param {string} errorId error id
     */
    displayError(code, message, errorId) {
        var text = "Bei der letzten Anfrage ist ein Fehler aufgetreten: ";
        text += message;
        text += " (Code: " + code;
        text += ", ID: " + errorId;
        text += ").";
        text += " Bitte benachrichtigen Sie den Administrator. Beachten Sie, dass die durchgeführten Änderungen möglicherweise nicht gespeichert wurden.";

        document.getElementById("error-alert").textContent = text;
        document.getElementById("error-alert").classList.remove("hidden");

        document.getElementById("error-alert").animate(
            [
                { opacity: '0' },
                { opacity: '1' }
            ],
            {
                duration: 500,
            }
        );
    }

    /**
     * remove alert from ui
     */
    clearError() {
        document.getElementById("error-alert").classList.add("hidden");
    }
}