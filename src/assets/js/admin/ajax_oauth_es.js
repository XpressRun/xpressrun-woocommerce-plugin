class EnableButton {
    element;
    user;
    constructor(id) {
        const self = this;
        this.element = document.getElementById(id);
        this.user = JSON.parse(this.element.dataset?.user);
        this.element.addEventListener("click", (e) => this.handleClick(e,self));
    }

    handleClick(e,self){
        window.location.href = self.getUrl;
    }

   get getUrl(){

        const register_url = new URL(this.user.endpoint);

        register_url.searchParams.append("full_name", this.user.user_info.first_name + " " + this.user.user_info.last_name);
        register_url.searchParams.append("email", this.user.user_info.email);
        register_url.searchParams.append("token", this.user.token);
        register_url.searchParams.append("company_name", this.user.company_info.name);
        register_url.searchParams.append("phone_number", this.user.user_info.mobile_phone);
        register_url.searchParams.append("provider_name", "WOOCOMMERCE");
        register_url.searchParams.append("provider_img", "https://ps.w.org/woocommerce/assets/banner-1544x500.png?rev=2366418");

        return register_url;
   }
}
new EnableButton("woocommerce_xpressrun_redirect");