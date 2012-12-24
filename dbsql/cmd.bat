mysqldump --no-data fotavia > dbsql\schema.sql
mysqldump --compact --no-create-info fotavia users temp_users email_optouts follows > dbsql\data.sql

-- helpful queries
-- see who's following whom
select u.username, p.username from follows f inner join users u on f.follower_user_id=u.user_id inner join users p on f.leader_user_id=p.user_id;

-- most popular photos (based on num views)
select photo_id, u.username, left(title,20) as title, num_views, CONCAT("http://fotavia.com", left(page_url, 50)) as url from photos p inner join users u on p.owner_id=u.user_id order by num_views desc limit 15;

