<?php

class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $slug;
    public $description;
    public $price;
    public $stock;
    public $image_url;
    public $emoji;
    public $promo_price;
    public $badge;
    public $tags;
    public $image_data;
    public $image_mime_type;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT p.*, c.slug as category_slug 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.is_active = TRUE
                  ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getSingle() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->slug = $row['slug'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->stock = $row['stock'];
            $this->category_id = $row['category_id'];
            $this->image_url = $row['image_url'];
            $this->emoji = $row['emoji'];
            $this->promo_price = $row['promo_price'];
            $this->badge = $row['badge'];
            $this->tags = $row['tags'];
            $this->image_data = $row['image_data'];
            $this->image_mime_type = $row['image_mime_type'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  (category_id, name, slug, description, price, promo_price, badge, tags, stock, image_url, image_data, image_mime_type, emoji)
                  VALUES (:category_id, :name, :slug, :description, :price, :promo_price, :badge, CAST(:tags AS jsonb), :stock, :image_url, :image_data, :image_mime_type, :emoji)";
        
        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->slug=htmlspecialchars(strip_tags($this->slug));
        $this->description=htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":promo_price", $this->promo_price);
        $stmt->bindParam(":badge", $this->badge);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindValue(":image_data", $this->image_data, $this->image_data === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        $stmt->bindParam(":image_mime_type", $this->image_mime_type);
        $stmt->bindParam(":emoji", $this->emoji);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, slug=:slug, description=:description, price=:price, promo_price=:promo_price, badge=:badge, tags=CAST(:tags AS jsonb), stock=:stock, image_url=:image_url, image_data=:image_data, image_mime_type=:image_mime_type, emoji=:emoji
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->slug=htmlspecialchars(strip_tags($this->slug));
        $this->description=htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":slug", $this->slug);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":promo_price", $this->promo_price);
        $stmt->bindParam(":badge", $this->badge);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindValue(":image_data", $this->image_data, $this->image_data === null ? PDO::PARAM_NULL : PDO::PARAM_LOB);
        $stmt->bindParam(":image_mime_type", $this->image_mime_type);
        $stmt->bindParam(":emoji", $this->emoji);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
