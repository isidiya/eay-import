/**
 * Created by timur on 20.12.2018.
 */
const MONTH_NAMES = [
	'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
	'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];

const article_mixin =  {
    data () {
        return {
            articles: null,
            widget: null,
            loading: true,
            errored: false,
        }
    },
    mounted () {
        axios
		.get(this.widget_url)
		.then(response =>
        {
			this.articles =  response.data.articles
			this.widget =  response.data.widget
		})
		.catch(error => {
			console.log(error)
			this.errored = true
		})
		.finally(() =>{
			this.loading = false
			this.widget.widgetData = JSON.parse(this.widget.widgetData);
		})
    },
	updated: function () {
		if (typeof this.dot_classes !== 'undefined') {
				this.dotFunction()
			}
	},
	methods:{
		subStr (value,lenStr=20) {
			var regex = /(<([^>]+)>)/ig
			,   result = value.replace(regex, "");
			if(result.length>lenStr){
			return result.substring(0,lenStr) + '...';
			} else { return result; }
		},
		publishTimeDate(publishTime,time=false) {
			var publishTimeAgo = new Date(publishTime);
			var dateNow = new Date();
			var secondDiff = (dateNow - publishTimeAgo)/ 1000;
			if(secondDiff < 119){//now
				return 'now';
			}else if(secondDiff < (60*60)){//first hour
				secondDiff = Math.round(secondDiff / 60);
				return secondDiff+'m ago';
			}else if(secondDiff < (60*60*24)){//24 hour
				secondDiff = Math.round(secondDiff / 3600);
				return secondDiff+'h ago';
			}else{
				var day = publishTimeAgo.getDate(publishTime);
				var month = MONTH_NAMES[publishTimeAgo.getMonth(publishTime)];
				var year = publishTimeAgo.getFullYear(publishTime);
				var hours = publishTimeAgo.getHours(publishTime);
				var minutes = publishTimeAgo.getMinutes(publishTime);
				var hoursAndMinutes="";
				if(time){
					var ampm = hours >= 12 ? 'PM' : 'AM';
						hours = hours % 12;
						hours = hours ? hours : 12; // the hour '0' should be '12'
						minutes = minutes < 10 ? '0'+minutes : minutes;
						hoursAndMinutes = '| ' + hours + ':' + minutes + ' ' + ampm;
				}

				return day + ' ' +  month + ' ' +  year + ' ' +  hoursAndMinutes;
			}
        },
		targetValue(article){
			if(article.permalink.indexOf("http://") == 0 || article.permalink.indexOf("https://") == 0){
				return '_blank';
			} else {
				return '_self';
			}
		},
		dotFunction(){
			for (index = 0; index < this.dot_classes.length; ++index) {
				var classes = this.dot_classes[index];
				var heights = this.dot_heights[index];
				jQuery(classes).dotdotdot({
					wrap: 'word',
					ellipsis: '... ',
					fallbackToLetter: true,
					height:heights
				});
			}

		},
		GenerateMediaTag(value,thumb=false,iframe=false){
			var media = '';
			switch(value.mediaType) {
				case '0':
					var mediaStyle = '';
					$img_crop = JSON.parse(value.imageCropping);
					if (typeof $img_crop.focal_point != "undefined") {
						$selectX = ($img_crop.focal_point.selectx1 + 1) *50;
						$selectY = 100 - ($img_crop.focal_point.selecty1 + 1)*50;
						mediaStyle = 'object-position:' + $selectX + '% ' + $selectY +'%;';
					}
					if(thumb){
						media = "<img style='"+mediaStyle+"' src='" +value.imageThumbPath +"' onerror=\"this.src='/theme_metro/images/no-image.png'\" />";
					}else{
						media = "<img style='"+mediaStyle+"' src='" +value.imagePath +"' onerror=\"this.src='/theme_metro/images/no-image.png'\" />";
					}
					break;
				case '1':
					media = "<img src='/theme_metro/images/no-image.png' />";
					break;
				case '2':
					if(iframe){
						media = "<iframe width='100%' height='auto' src='https://www.youtube.com/embed/" +value.imageThumbPath +"' frameborder='0' allow=						 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
					}
					else {
						media = "<img src='https://img.youtube.com/vi/" +value.imageThumbPath +"/hqdefault.jpg' onerror=\"this.src='/theme_metro/images/no-image.png'						 \" /><div class='vid-icon'><img src='/theme_metro/images/play-icon.png' alt='icon video'> </div>";
					}
					break;
				case '6':
					if(iframe){
						media = "<iframe width='100%' frameborder='0' allowfullscreen  src='https://vod-platform.net/Embed/" +value.imageThumbPath +"' frameborder='0'></iframe>";
					}
					else {
						media = "<img src='https://vod-platform.net/image/" +value.imageThumbPath +"' onerror=\"this.src='/theme_metro/images/no-image.png'\" /><div class='vid-icon'><img src='/theme_metro/images/play-icon.png' alt='icon video'> </div>";
					}
					break;
				default:
					media = "<img src='/theme_metro/images/no-image.png' />";
			}
			return media;
		}
	},
    filters: {
        subStr (value,lenStr=20) {
			var regex = /(<([^>]+)>)/ig
			,   result = value.replace(regex, "");
			if(result.length>lenStr){
			return result.substring(0,lenStr) + '...';
			} else { return result; }
		},
        publishTimeDate(publishTime) {
			var publishTimeAgo = new Date(publishTime);
			var dateNow = new Date();
			var secondDiff = (dateNow - publishTimeAgo)/ 1000;
			if(secondDiff < 119){//now
				return 'now';
			}else if(secondDiff < (60*60)){//first hour
				secondDiff = Math.round(secondDiff / 60);
				return secondDiff+'m ago';
			}else if(secondDiff < (60*60*24)){//24 hour
				secondDiff = Math.round(secondDiff / 3600);
				return secondDiff+'h ago';
			}else{
				var day = publishTimeAgo.getDate(publishTime);
				var month = MONTH_NAMES[publishTimeAgo.getMonth(publishTime)];
				var year = publishTimeAgo.getFullYear(publishTime);
				var hours = publishTimeAgo.getHours(publishTime);
				var minutes = publishTimeAgo.getMinutes(publishTime);
				return day + ' ' +  month +' ' +  year;
			}
        }
    },
};
const html_mixin =  {
    data () {
        return {
            html: null,
			widget: null,
			loading: true,
			errored: false
        }
    },
	mounted () {
		axios
		.get(this.widget_url)
		.then(response =>
				{
					this.html =  response.data.html
					this.widget =  response.data.widget
				})
		.catch(error => {
			console.log(error)
			this.errored = true
		})
		.finally(() =>{
			this.loading = false
			this.widget.widgetData = JSON.parse(this.widget.widgetData);
		})
	}
};
const menu_mixin =  {
    data () {
        return {
            menu: null,
			widget: null,
			loading: true,
			errored: false
        }
    },
	mounted () {
		axios
		.get(this.widget_url)
		.then(response =>
				{
					this.menu =  response.data.menu
					this.widget =  response.data.widget
				})
		.catch(error => {
			console.log(error)
			this.errored = true
		})
		.finally(() =>{
			this.loading = false
			this.widget.widgetData = JSON.parse(this.widget.widgetData);
		})
	}
}
